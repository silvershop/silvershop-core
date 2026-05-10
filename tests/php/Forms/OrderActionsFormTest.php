<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use Omnipay\Common\GatewayFactory;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\AbstractGateway;
use PHPUnit\Framework\MockObject\MockObject;
use SilverShop\Extension\OrderManipulationExtension;
use SilverShop\Forms\OrderActionsForm;
use SilverShop\Forms\OrderActionsFormValidator;
use SilverShop\Model\Order;
use SilverShop\Page\CheckoutPage;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\Director;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Security\RandomGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class OrderActionsFormTest extends FunctionalTest
{
    protected static $fixture_file = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];

    // This seems to be required, because we query the OrderItem table and thus this gets included…
    // TODO: Remove once we figure out how to circumvent that…
    protected static $extra_dataobjects = [
        CustomProduct_OrderItem::class,
    ];

    protected Order $order;

    protected CheckoutPage $checkoutPage;

    protected function setUp(): void
    {
        parent::setUp();
        ShopTestBootstrap::setConfiguration();

        $this->logInWithPermission('ADMIN');
        // create order from fixture and persist to DB
        $this->order = $this->objFromFixture(Order::class, "unpaid");
        $this->order->write();


        // create checkoug page from fixture and publish it
        $this->checkoutPage = $this->objFromFixture(CheckoutPage::class, "checkout");
        $this->checkoutPage->publishSingle();

        $this->logOut();

        OrderManipulationExtension::add_session_order($this->order);
        Config::modify()->set(Payment::class, 'allowed_gateways', ['Dummy']);
        Config::modify()->merge(GatewayInfo::class, 'Dummy', [
            'is_offsite' => false
        ]);
    }

    /**
     * OrderActionsForm action is at `checkout/ActionsForm`. Prime the session with a GET
     * to the order page so the CSRF token exists, then POST to the ActionsForm endpoint.
     *
     * @param  array<string, mixed>  $body
     */
    private function submitOrderActionsForm(array $body): \SilverStripe\Control\HTTPResponse
    {
        $orderUrl = $this->checkoutPage->Link('order/' . $this->order->ID);
        $this->get($orderUrl);

        $securityID = $this->session()->get('SecurityID');
        if (!$securityID) {
            $securityID = (new RandomGenerator())->randomToken('sha1');
            $this->session()->set('SecurityID', $securityID);
        }

        $body['SecurityID'] = $securityID;

        $actionsFormUrl = $this->checkoutPage->Link('ActionsForm');
        return Director::test($actionsFormUrl, $body, $this->session(), 'POST');
    }

    public function testOffsitePayment(): void
    {
        Config::modify()->set(GatewayInfo::class, 'Dummy', ['is_offsite' => true]);
        $mockObject = $this->buildPaymentGatewayStub(true, 'test-' . $this->order->ID, true);
        Injector::inst()->registerService($this->stubGatewayFactory($mockObject), GatewayFactory::class);

        $httpResponse = $this->submitOrderActionsForm(
            [
                'action_dopayment' => true,
                'OrderID' => $this->order->ID,
                'PaymentMethod' => 'Dummy',
            ]
        );

        $this->order = Order::get()->byID($this->order->ID);
        // There should be a new payment
        $this->assertEquals(1, $this->order->Payments()->count());
        // The status of the payment should be pending purchase, as there's a redirect to the offsite gateway
        $this->assertEquals('PendingPurchase', $this->order->Payments()->first()->Status);
        // The response we get from submitting the form should be a redirect to the offsite payment form
        $this->assertEquals('http://paymentprovider/test/offsiteform', $httpResponse->getHeader('Location'));
    }

    public function testOnsitePayment(): void
    {
        $mockObject = $this->buildPaymentGatewayStub(true, 'test-' . $this->order->ID, false);
        Injector::inst()->registerService($this->stubGatewayFactory($mockObject), GatewayFactory::class);

        $httpResponse = $this->submitOrderActionsForm(
            [
                'action_dopayment' => true,
                'OrderID' => $this->order->ID,
                'PaymentMethod' => 'Dummy',
                'type' => 'visa',
                'name' => 'Tester Mc. Testerson',
                'number' => '4242424242424242',
                'expiryMonth' => 10,
                'expiryYear' => date('Y') + 1,
                'cvv' => 123,
            ]
        );

        $this->order = Order::get()->byID($this->order->ID);
        // There should be a new payment
        $this->assertEquals(1, $this->order->Payments()->count());
        // The status of the payment should be Captured
        $this->assertEquals('Captured', $this->order->Payments()->first()->Status);
        // The response we get from submitting the form should be a redirect to the paid order
        $this->assertStringEndsWith(
            $this->checkoutPage->Link('order/' . $this->order->ID),
            $httpResponse->getHeader('location')
        );
    }

    public function testValidation(): void
    {
        $orderActionsFormValidator = OrderActionsFormValidator::create('PaymentMethod');
        $orderActionsForm = OrderActionsForm::create(
            ModelAsController::controller_for($this->checkoutPage),
            'ActionsForm',
            $this->order
        );
        $orderActionsFormValidator->setForm($orderActionsForm);
        $orderActionsFormValidator->php(
            [
                'OrderID' => $this->order->ID,
                'PaymentMethod' => 'Dummy',
                'type' => 'visa',
                'name' => 'Tester Mc. Testerson',
                'number' => '4242424242424242'
            ]
        );

        $requiredCount = 0;
        foreach ($orderActionsFormValidator->getErrors() as $error) {
            if ($error['messageType'] == 'required') {
                ++$requiredCount;
            }
        }

        // 3 required fields missing
        $this->assertEquals(3, $requiredCount);
    }

    protected function stubGatewayFactory($stubGateway): MockObject
    {
        $mock = $this->getMockBuilder(GatewayFactory::class)->getMock();
        $mock->expects($this->any())->method('create')->will($this->returnValue($stubGateway));
        return $mock;
    }

    protected function buildPaymentGatewayStub(
        $successValue,
        $transactionReference,
        $isRedirect = true
    ): MockObject {
        //--------------------------------------------------------------------------------------------------------------
        // request and response

        $mockResponse = $this->getMockBuilder(AbstractResponse::class)
            ->disableOriginalConstructor()->getMock();

        $mockResponse->expects($this->any())
            ->method('isSuccessful')->will($this->returnValue($successValue));

        $mockResponse->expects($this->any())
            ->method('isRedirect')->will($this->returnValue($isRedirect));

        $mockResponse->expects($this->any())
            ->method('getRedirectResponse')->will(
                $this->returnValue(
                    new RedirectResponse('http://paymentprovider/test/offsiteform')
                )
            );

        $mockResponse->expects($this->any())
            ->method('getTransactionReference')->will($this->returnValue($transactionReference));

        $mockRequest = $this->getMockBuilder(AbstractRequest::class)
            ->disableOriginalConstructor()->getMock();

        $mockRequest->expects($this->any())
            ->method('send')->will($this->returnValue($mockResponse));

        $mockRequest->expects($this->any())
            ->method('getTransactionReference')->will($this->returnValue($transactionReference));


        //--------------------------------------------------------------------------------------------------------------
        // Build the gateway

        $mock = $this->getMockBuilder(AbstractGateway::class)
            ->addMethods(['purchase'])
            ->onlyMethods(['supportsCompletePurchase', 'getName'])
            ->getMock();

        $mock->expects($this->any())
            ->method('purchase')
            ->will($this->returnValue($mockRequest));

        $mock->expects($this->any())
            ->method('getName')
            ->willReturn('Dummy');

        $mock->expects($this->any())
            ->method('supportsCompletePurchase')
            ->will($this->returnValue($isRedirect));

        return $mock;
    }
}

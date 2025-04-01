<?php

namespace SilverShop\Tests\Checkout;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Message;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverShop\Tests\TestGatewayFactory;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestSession;
use SilverStripe\Omnipay\Model\Payment;
use Symfony\Component\HttpFoundation\Request;

class ShopPaymentTest extends FunctionalTest
{
    protected static $fixture_file = [
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    ];
    public static $disable_theme = true;
    protected $autoFollowRedirection = false;

    /** @var MockHandler */
    protected $mockHandler;

    public function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();

        //set supported gateways
        Config::modify()->set(
            Payment::class,
            'allowed_gateways',
            [
                'Dummy', //onsite
                'Manual', //manual
                'PaymentExpress_PxPay', //offsite
                'PaymentExpress_PxPost' //onsite
            ]
        )->set(
            Injector::class,
            'Omnipay\Common\GatewayFactory',
            [
                'class' => TestGatewayFactory::class
            ]
        );

        TestGatewayFactory::$httpClient = $this->getHttpClient();
        TestGatewayFactory::$httpRequest = $this->getHttpRequest();

        $this->logInWithPermission('ADMIN');
        $this->objFromFixture(Product::class, "socks")->publishSingle();
        $this->objFromFixture(CheckoutPage::class, "checkout")->publishSingle();
        $this->objFromFixture(CartPage::class, "cart")->publishSingle();
    }

    public function testManualPayment()
    {
        $order = $this->objFromFixture(Order::class, 'unpaid');
        $payment = Payment::create()->init('Manual', 100.00, 'NZD');
        $payment->OrderID = $order->ID;
        $payment->write();

        // Process payment
        $processor = OrderProcessor::create($order);
        $response = $processor->makePayment('Manual', []);

        $this->assertTrue($response->isSuccessful(), 'Manual payment should be successful');
        $this->assertEquals('Created', $payment->Status, 'Payment status should be Created');
    }

    public function testOnsitePayment()
    {
        $order = $this->objFromFixture(Order::class, 'unpaid');

        // Process onsite payment with dummy gateway
        $processor = OrderProcessor::create($order);
        $response = $processor->makePayment(
            'Dummy',
            [
                'number' => '4242424242424242',
                'expiryMonth' => '12',
                'expiryYear' => '2025',
                'cvv' => '123'
            ]
        );
        $processor->completePayment();
        $this->assertTrue($response->isSuccessful(), 'Onsite payment should be successful');
        $this->assertFalse($response->isRedirect(), 'Should not be a redirect for onsite payment');
        $this->assertTrue($order->isPaid(), 'Order should be marked as paid');
        $payment = $response->getPayment();
        $this->assertEquals('Captured', $payment->Status, 'Payment status should be Captured');
    }

    public function testOffsitePayment()
    {
        $order = $this->objFromFixture(Order::class, 'unpaid');

        // Process payment with dummy gateway
        $processor = OrderProcessor::create($order);
        $response = $processor->makePayment(
            'Dummy',
            [
                'number' => '4242424242424242',
                'expiryMonth' => '12',
                'expiryYear' => '2025',
                'cvv' => '123'
            ]
        );
        $processor->completePayment();

        $this->assertTrue($response->isSuccessful(), 'Onsite payment should be successful');
        $this->assertEquals('Paid', $order->Status, 'Order status should be Paid');
        $payment = $response->getPayment();
        $this->assertEquals('Captured', $payment->Status, 'Payment status should be Captured');
    }

    public function testOffsitePaymentWithGatewayCallback()
    {
        //set up cart
        $cart = ShoppingCart::singleton()
            ->setCurrent($this->objFromFixture(Order::class, "cart"))
            ->current();
        //collect checkout details
        $cart->update(
            [
                'FirstName' => 'Foo',
                'Surname' => 'Bar',
                'Email' => 'foo@example.com',
            ]
        );
        $cart->write();
        //pay for order with external gateway
        $processor = OrderProcessor::create($cart);
        $this->setMockHttpResponse('paymentexpress/tests/Mock/PxPayPurchaseSuccess.txt');
        $response = $processor->makePayment("PaymentExpress_PxPay", []);
        //gateway responds (in a different session)
        $oldsession = $this->mainSession;
        $this->mainSession = new TestSession();
        ShoppingCart::singleton()->clear();
        $this->setMockHttpResponse('paymentexpress/tests/Mock/PxPayCompletePurchaseSuccess.txt');
        $this->getHttpRequest()->query->replace(['result' => 'abc123']);
        $identifier = $response->getPayment()->Identifier;

        //bring back client session
        $this->mainSession = $oldsession;
        // complete the order
        $response = $this->get("paymentendpoint/$identifier/complete", $oldsession->session());

        //reload cart as new order
        $order = Order::get()->byId($cart->ID);
        $this->assertFalse($order->isCart(), "order is no longer in cart");
        $this->assertTrue($order->isPaid(), "order is paid");
        $this->assertNull($this->mainSession->session()->get("shoppingcartid"), "cart session id should be removed");
        $this->assertNotEquals(404, $response->getStatusCode(), "We shouldn't get page not found");
    }

    protected $payment;
    protected $httpClient;
    protected $httpRequest;

    protected function getHttpClient()
    {
        if (null === $this->httpClient) {
            if ($this->mockHandler === null) {
                $this->mockHandler = new MockHandler();
            }

            $guzzle = new Client([
                'handler' => $this->mockHandler,
            ]);

            $this->httpClient = new \Omnipay\Common\Http\Client(new \Http\Adapter\Guzzle7\Client($guzzle));
        }

        return $this->httpClient;
    }

    protected function getHttpRequest()
    {
        if (null === $this->httpRequest) {
            $this->httpRequest = new Request;
        }

        return $this->httpRequest;
    }

    protected function setMockHttpResponse($paths)
    {
        if ($this->mockHandler === null) {
            throw new Exception('HTTP client not initialised before adding mock response.');
        }

        $testspath = BASE_PATH . '/vendor/omnipay';

        foreach ((array)$paths as $path) {
            $this->mockHandler->append(
                Message::parseResponse(file_get_contents("{$testspath}/{$path}"))
            );
        }

        return $this->mockHandler;
    }
}

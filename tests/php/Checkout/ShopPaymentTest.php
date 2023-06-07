<?php

namespace SilverShop\Tests\Checkout;

use GuzzleHttp\Psr7\Message;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestSession;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Tests\Service\TestGatewayFactory;

class ShopPaymentTest extends FunctionalTest
{
    protected static $fixture_file = array(
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    );
    public static $disable_theme = true;
    protected $autoFollowRedirection = false;

    /** @var \GuzzleHttp\Handler\MockHandler */
    protected $mockHandler = null;

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

        //publish products
        $this->logInWithPermission('ADMIN');
        $this->objFromFixture(Product::class, "socks")->publishSingle();
        $this->objFromFixture(CheckoutPage::class, "checkout")->publishSingle();
        $this->objFromFixture(CartPage::class, "cart")->publishSingle();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testManualPayment()
    {
        $this->markTestIncomplete("Process a manual payment");
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOnsitePayment()
    {
        $this->markTestIncomplete("Process an onsite payment");
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOffsitePayment()
    {
        $this->markTestIncomplete("Process an off-site payment");
    }

    public function testOffsitePaymentWithGatewayCallback()
    {
        //set up cart
        $cart = ShoppingCart::singleton()
            ->setCurrent($this->objFromFixture(Order::class, "cart"))
            ->current();
        //collect checkout details
        $cart->update(
            array(
                'FirstName' => 'Foo',
                'Surname' => 'Bar',
                'Email' => 'foo@example.com',
            )
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
        $this->getHttpRequest()->query->replace(array('result' => 'abc123'));
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
                $this->mockHandler = new \GuzzleHttp\Handler\MockHandler();
            }

            $guzzle = new \GuzzleHttp\Client([
                'handler' => $this->mockHandler,
            ]);

            $this->httpClient = new \Omnipay\Common\Http\Client(new \Http\Adapter\Guzzle7\Client($guzzle));
        }

        return $this->httpClient;
    }

    protected function getHttpRequest()
    {
        if (null === $this->httpRequest) {
            $this->httpRequest = new \Symfony\Component\HttpFoundation\Request;
        }

        return $this->httpRequest;
    }

    protected function setMockHttpResponse($paths)
    {
        if ($this->mockHandler === null) {
            throw new \Exception('HTTP client not initialised before adding mock response.');
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

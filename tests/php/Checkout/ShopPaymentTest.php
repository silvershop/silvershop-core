<?php

namespace SilverShop\Tests\Checkout;


use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverShop\Page\CartPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestSession;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\PaymentService;
use Symfony\Component\HttpFoundation\Request;

class ShopPaymentTest extends FunctionalTest
{
    protected static $fixture_file = array(
        '../Fixtures/Pages.yml',
        '../Fixtures/shop.yml',
    );
    public static $disable_theme = true;


    public function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();

        //set supported gateways
        Payment::config()->allowed_gateways = array(
            'Dummy', //onsite
            'Manual', //manual
            'PaymentExpress_PxPay', //offsite
            'PaymentExpress_PxPost' //onsite
        );

        PaymentService::setHttpClient($this->getHttpClient());
        PaymentService::setHttpRequest($this->getHttpRequest());

        //publish products
        $this->objFromFixture(Product::class, "socks")->publishSingle();
        $this->objFromFixture(CheckoutPage::class, "checkout")->publishSingle();
        $this->objFromFixture(CartPage::class, "cart")->publishSingle();
    }

    public function testManualPayment()
    {
        $this->markTestIncomplete("Process a manual payment");
    }

    public function testOnsitePayment()
    {
        $this->markTestIncomplete("Process an onsite payment");
    }

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
        $response = $processor->makePayment("PaymentExpress_PxPay", array());
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
        $response = $this->get("paymentendpoint/$identifier/complete");

        //reload cart as new order
        $order = Order::get()->byId($cart->ID);
        $this->assertFalse($order->isCart(), "order is no longer in cart");
        $this->assertTrue($order->isPaid(), "order is paid");
        $this->assertNull($this->mainSession->session()->get("shoppingcartid"), "cart session id should be removed");
        $this->assertNotEquals(404, $response->getStatusCode(), "We shouldn't get page not found");

        $this->markTestIncomplete("Should assert other things");
    }

    protected $payment;
    protected $httpClient;
    protected $httpRequest;

    protected function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    public function getHttpRequest()
    {
        if (null === $this->httpRequest) {
            $this->httpRequest = new Request();
        }

        return $this->httpRequest;
    }

    protected function setMockHttpResponse($paths)
    {
        $testspath = BASE_PATH . '/vendor/omnipay';
        $mock = new MockPlugin(null, true);
        $this->getHttpClient()->getEventDispatcher()->removeSubscriber($mock);
        foreach ((array)$paths as $path) {
            $mock->addResponse($testspath . '/' . $path);
        }
        $this->getHttpClient()->getEventDispatcher()->addSubscriber($mock);

        return $mock;
    }
}

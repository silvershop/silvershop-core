<?php

namespace SilverShop\Tests\Extension;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Extension\MemberExtension;
use SilverShop\Extension\SteppedCheckoutExtension;
use SilverShop\Forms\PaymentForm;
use SilverShop\Model\Order;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\CheckoutPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\Model\Product\CustomProduct_OrderItem;
use SilverShop\Tests\ShopTest;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class SteppedCheckoutExtensionTest extends FunctionalTest
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

    protected static bool $use_draft_site = true; //so we don't need to publish
    protected $autoFollowRedirection = false;

    protected CheckoutPageController $checkout;
    protected Product $socks;
    protected Order $cart;

    public function setUp(): void
    {
        parent::setUp();
        $this->logInWithPermission('ADMIN');

        Config::modify()->merge(GatewayInfo::class, 'Dummy', [
            'is_offsite' => false
        ]);

        ShopTest::setConfiguration();
        ShoppingCart::singleton()->clear();
        //set up steps
        SteppedCheckoutExtension::setupSteps(); //use default steps

        $this->socks = $this->objFromFixture(Product::class, "socks");
        $this->socks->publishSingle();

        /**
         * @var CheckoutPage $dataObject
         */
        $dataObject = $this->objFromFixture(CheckoutPage::class, "checkout");
        $dataObject->publishSingle();
        $this->checkout = CheckoutPageController::create($dataObject);

        $this->cart = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($this->cart);
    }

    public function testTemplateFunctionsForFirstStep(): void
    {
        //put us at the first step index == membership
        $this->checkout->handleRequest($this->buildTestRequest(''));

        $this->assertTrue($this->checkout->StepExists('membership'));
        $this->assertFalse($this->checkout->IsPastStep('membership'));
        $this->assertTrue($this->checkout->IsCurrentStep('membership'));
        $this->assertFalse($this->checkout->IsFutureStep('membership'));

        $this->checkout->NextStepLink();

        $this->assertTrue($this->checkout->StepExists('contactdetails'));
        $this->assertFalse($this->checkout->IsPastStep('contactdetails'));
        $this->assertFalse($this->checkout->IsCurrentStep('contactdetails'));
        $this->assertTrue($this->checkout->IsFutureStep('contactdetails'));
    }

    public function testTemplateFunctionsForOtherSteps(): void
    {
        $this->checkout->handleRequest($this->buildTestRequest('summary')); //change to summary step
        $this->assertTrue($this->checkout->StepExists('summary'));
        $this->assertFalse($this->checkout->IsPastStep('summary'));
        $this->assertTrue($this->checkout->IsCurrentStep('summary'));
        $this->assertFalse($this->checkout->IsFutureStep('summary'));

        $this->assertFalse($this->checkout->StepExists('nosuchstep'));
    }

    public function testMembershipStep(): void
    {
        $this->logOut();
        ShoppingCart::singleton()->clear();
        $data = [
            'FirstName' => 'Michael',
            'Surname' => 'Black',
            'Email' => 'mb@example.com',
            'Password' => [
                '_Password' => 'pass1234',
                '_ConfirmPassword' => 'pass1234',
            ],
            'action_docreateaccount' => 'Create New Account',
        ];
        $this->post('/checkout/CreateAccountForm', $data); //redirect to next step

        $member = MemberExtension::get_by_identifier("mb@example.com");
        $this->assertTrue((boolean)$member, "Check new account was created");
        $this->assertEquals('Michael', $member->FirstName);
        $this->assertEquals('Black', $member->Surname);
    }

    public function testContactDetails(): void
    {
        $this->post(
            '/checkout/ContactDetailsForm',
            [
                'SilverShop-Checkout-Component-CustomerDetails_Email' => 'p.richardson@example.com',
                'SilverShop-Checkout-Component-CustomerDetails_FirstName' => 'Pauline',
                'SilverShop-Checkout-Component-CustomerDetails_Surname' => 'Richardson',
            ]
        );
        $order = ShoppingCart::curr();
        $this->assertEquals(
            'Pauline',
            $order->FirstName,
            'Order FirstName should be updated'
        );
        $this->assertEquals(
            'Richardson',
            $order->Surname,
            'Order Surname should be updated'
        );
        $this->assertEquals(
            'p.richardson@example.com',
            $order->Email,
            'Order Email should be updated'
        );
    }

    public function testShippingAddress(): void
    {
        $this->post(
            '/checkout/ShippingAddressForm',
            [
                'SilverShop-Checkout-Component-ShippingAddress_Company' => 'Acme Inc',
                'SilverShop-Checkout-Component-ShippingAddress_Address' => '2b Baba place',
                'SilverShop-Checkout-Component-ShippingAddress_AddressLine2' => 'Level 2',
                'SilverShop-Checkout-Component-ShippingAddress_City' => 'Newton',
                'SilverShop-Checkout-Component-ShippingAddress_State' => 'Wellington',
                'SilverShop-Checkout-Component-ShippingAddress_Country' => 'NZ',
                'SilverShop-Checkout-Component-ShippingAddress_Phone' => '12345678'
            ]
        );
        $order = ShoppingCart::curr();
        $address = $order->getShippingAddress();
        $this->assertEquals(
            'Acme Inc',
            $address->Company,
            'Shipping Company should be updated'
        );
        $this->assertEquals(
            '2b Baba place',
            $address->Address,
            'Shipping address should be updated'
        );
        $this->assertEquals(
            'Level 2',
            $address->AddressLine2,
            'Shipping address line 2 should be updated'
        );
        $this->assertEquals(
            'Newton',
            $address->City,
            'Shipping city should be updated'
        );
        $this->assertEquals(
            'Wellington',
            $address->State,
            'Shipping state should be updated'
        );
        $this->assertEquals(
            'NZ',
            $address->Country,
            'Shipping country should be updated'
        );
        $this->assertEquals(
            '12345678',
            $address->Phone,
            'Shipping phone number should be updated'
        );
    }

    public function testBillingAddress(): void
    {
        $this->post(
            '/checkout/BillingAddressForm',
            [
                'SilverShop-Checkout-Component-BillingAddress_Address' => '3 Art Cresent',
                'SilverShop-Checkout-Component-BillingAddress_AddressLine2' => '',
                'SilverShop-Checkout-Component-BillingAddress_City' => 'Walkworth',
                'SilverShop-Checkout-Component-BillingAddress_State' => 'New Caliphoneya',
                'SilverShop-Checkout-Component-BillingAddress_Country' => 'ZA',
                'SilverShop-Checkout-Component-BillingAddress_Phone' => '12345678'
            ]
        );
        $order = ShoppingCart::curr();
        $address = $order->BillingAddress();
        $this->assertEquals(
            '3 Art Cresent',
            $address->Address,
            'Billing address should be updated'
        );
        $this->assertEquals(
            'Walkworth',
            $address->City,
            'Billing city should be updated'
        );
        $this->assertEquals(
            'New Caliphoneya',
            $address->State,
            'Billing state should be updated'
        );
        $this->assertEquals(
            'ZA',
            $address->Country,
            'Billing country should be updated'
        );
        $this->assertEquals(
            '12345678',
            $address->Phone,
            'Billing phone should be updated'
        );
    }

    public function testPaymentMethod(): void
    {
        $data = [
            'PaymentMethod' => 'Dummy',
            'action_setpaymentmethod' => 1,
        ];
        $this->post('/checkout/PaymentMethodForm', $data);
        $this->assertEquals('Dummy', Checkout::get($this->cart)->getSelectedPaymentMethod());
    }

    public function testSummary(): void
    {
        $this->checkout->handleRequest($this->buildTestRequest('summary'));
        /**
         * @var PaymentForm $paymentForm
         */
        $paymentForm = $this->checkout->ConfirmationForm();
        $data = [
            'Notes' => 'Leave it around the back',
            'ReadTermsAndConditions' => 1,
        ];
        $member = $this->objFromFixture(Member::class, "joebloggs");
        Security::setCurrentUser($member);

        Checkout::get($this->cart)->setPaymentMethod("Dummy"); //a selected payment method is required
        $paymentForm->loadDataFrom($data);
        $this->assertTrue($paymentForm->validationResult()->isValid(), "Checkout data is valid");
        $httpResponse = $this->post('/checkout/ConfirmationForm', $data);
        $this->assertEquals('Cart', $this->cart->Status, "Order is still in cart");

        $order = Order::get()->byID($this->cart->ID);

        $this->assertEquals("Leave it around the back", $order->Notes);

        //redirect to make payment
        $this->assertEquals(302, $httpResponse->getStatusCode());
        $this->assertStringContainsString(
            '/checkout/payment',
            $httpResponse->getHeader('location')
        );
    }

    protected function buildTestRequest($url, $method = 'GET'): HTTPRequest
    {
        $httpRequest = new HTTPRequest($method, $url);
        $httpRequest->setSession($this->mainSession->session());
        return $httpRequest;
    }
}

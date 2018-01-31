<?php

namespace SilverShop\Tests\Extension;


use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Extension\MemberExtension;
use SilverShop\Extension\SteppedCheckoutExtension;
use SilverShop\Model\Order;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\CheckoutPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;


class SteppedCheckoutExtensionTest extends FunctionalTest
{
    protected static $fixture_file = array(
        __DIR__ . '/../Fixtures/Pages.yml',
        __DIR__ . '/../Fixtures/shop.yml',
    );
    protected static $use_draft_site = true; //so we don't need to publish
    protected $autoFollowRedirection = false;

    /** @var CheckoutPageController */
    protected $checkout;

    /** @var Product */
    protected $socks;

    /** @var Order */
    protected $cart;

    public function setUp()
    {
        parent::setUp();
        $this->logInWithPermission('ADMIN');

        ShopTest::setConfiguration();
        ShoppingCart::singleton()->clear();
        //set up steps
        SteppedCheckoutExtension::setupSteps(); //use default steps

        $this->socks = $this->objFromFixture(Product::class, "socks");
        $this->socks->publishSingle();

        /** @var CheckoutPage $checkoutpage */
        $checkoutpage = $this->objFromFixture(CheckoutPage::class, "checkout");
        $checkoutpage->publishSingle();
        $this->checkout = CheckoutPageController::create();
        $this->get('checkout');
        $this->checkout->handleRequest(new HTTPRequest("GET", "checkout"));

        $this->cart = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($this->cart);
    }

    public function testTemplateFunctionsForFirstStep()
    {
        //put us at the first step index == membership
        $indexRequest = new HTTPRequest('GET', "");
        $this->checkout = new CheckoutPageController(); // from 3.3 on it's necessary to have a clean controller here
        $this->checkout->handleRequest($indexRequest);


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

    public function testTemplateFunctionsForOtherSteps()
    {
        $summaryRequest = new HTTPRequest('GET', "summary");
        $this->checkout = new CheckoutPageController();
        $this->checkout->handleRequest($summaryRequest); //change to summary step
        $this->assertTrue($this->checkout->StepExists('summary'));
        $this->assertFalse($this->checkout->IsPastStep('summary'));
        $this->assertTrue($this->checkout->IsCurrentStep('summary'));
        $this->assertFalse($this->checkout->IsFutureStep('summary'));

        $this->assertFalse($this->checkout->StepExists('nosuchstep'));
    }

    public function testMembershipStep()
    {
        //this should still work if there is no cart
        ShoppingCart::singleton()->clear();

        $this->checkout->index();
        $this->checkout->membership();
        $this->post('/checkout/guestcontinue', array()); //redirect to next step
        $this->checkout->createaccount(new HTTPRequest('GET', "/checkout/createaccount"));

        $form = $this->checkout->MembershipForm();
        $data = array();
        $form->loadDataFrom($data);

        $data = array(
            'FirstName'              => 'Michael',
            'Surname'                => 'Black',
            'Email'                  => 'mb@example.com',
            'Password'               => array(
                '_Password'        => 'pass1234',
                '_ConfirmPassword' => 'pass1234',
            ),
            'action_docreateaccount' => 'Create New Account',
        );
        $response = $this->post('/checkout/CreateAccountForm', $data); //redirect to next step

        $member = MemberExtension::get_by_identifier("mb@example.com");
        $this->assertTrue((boolean)$member, "Check new account was created");
        $this->assertEquals('Michael', $member->FirstName);
        $this->assertEquals('Black', $member->Surname);
    }

    public function testContactDetails()
    {
        $user = $this->objFromFixture(Member::class, "joebloggs");
        Security::setCurrentUser($user);
        $this->checkout->contactdetails();
        $data = array(
            'FirstName'                => 'Pauline',
            'Surname'                  => 'Richardson',
            'Email'                    => 'p.richardson@example.com',
            'action_setcontactdetails' => 1,
        );
        $response = $this->post('/checkout/ContactDetailsForm', $data);

        $this->markTestIncomplete('check order has been updated');
    }

    public function testShippingAddress()
    {
        $user = $this->objFromFixture(Member::class, "joebloggs");
        Security::setCurrentUser($user);
        $this->checkout->shippingaddress();
        $data = array(
            'Address'           => '2b Baba place',
            'AddressLine2'      => 'Level 2',
            'City'              => 'Newton',
            'State'             => 'Wellington',
            'Country'           => 'NZ',
            'action_setaddress' => 1,
        );
        $response = $this->post('/checkout/AddressForm', $data);

        $this->markTestIncomplete('assertions!');
    }

    public function testBillingAddress()
    {
        $user = $this->objFromFixture(Member::class, "joebloggs");
        Security::setCurrentUser($user);
        $this->checkout->billingaddress();
        $data = array(
            'Address'                  => '3 Art Cresent',
            'AddressLine2'             => '',
            'City'                     => 'Walkworth',
            'State'                    => 'New Caliphoneya',
            'Country'                  => 'ZA',
            'action_setbillingaddress' => 1,
        );
        $response = $this->post('/checkout/AddressForm', $data);

        $this->markTestIncomplete('assertions!');
    }

    public function testPaymentMethod()
    {
        $data = array(
            'PaymentMethod'           => 'Dummy',
            'action_setpaymentmethod' => 1,
        );
        $response = $this->post('/checkout/PaymentMethodForm', $data);
        $this->assertEquals('Dummy', Checkout::get($this->cart)->getSelectedPaymentMethod());
    }

    public function testSummary()
    {
        $this->checkout->summary();
        $form = $this->checkout->ConfirmationForm();
        $data = array(
            'Notes'                  => 'Leave it around the back',
            'ReadTermsAndConditions' => 1,
        );
        $member = $this->objFromFixture(Member::class, "joebloggs");
        Security::setCurrentUser($member);

        Checkout::get($this->cart)->setPaymentMethod("Dummy"); //a selected payment method is required
        $form->loadDataFrom($data);
        $this->assertTrue($form->validate(), "Checkout data is valid");
        $response = $this->post('/checkout/ConfirmationForm', $data);
        $this->assertEquals('Cart', $this->cart->Status, "Order is still in cart");

        $order = Order::get()->byID($this->cart->ID);

        $this->assertEquals("Leave it around the back", $order->Notes);

        //redirect to make payment
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(
            Director::baseURL() . "checkout/payment",
            $response->getHeader('Location')
        );
    }
}

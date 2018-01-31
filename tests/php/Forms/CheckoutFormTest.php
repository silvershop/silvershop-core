<?php

namespace SilverShop\Tests\Forms;


use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\SinglePageCheckoutComponentConfig;
use SilverShop\Forms\CheckoutForm;
use SilverShop\Page\CheckoutPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;


class CheckoutFormTest extends SapphireTest
{
    public static $fixture_file = '../Fixtures/shop.yml';

    /** @var Product */
    protected $mp3player;

    /** @var Product */
    protected $socks;

    /** @var Product */
    protected $beachball;

    /** @var CheckoutPageController */
    protected $checkoutcontroller;

    public function setUp()
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTest::setConfiguration();
        $this->mp3player = $this->objFromFixture(Product::class, 'mp3player');
        $this->mp3player->publishSingle();
        $this->socks = $this->objFromFixture(Product::class, 'socks');
        $this->socks->publishSingle();
        $this->beachball = $this->objFromFixture(Product::class, 'beachball');
        $this->beachball->publishSingle();

        $this->checkoutcontroller = new CheckoutPageController();

        ShoppingCart::singleton()->add($this->socks); //start cart
    }

    public function testCheckoutForm()
    {
        $order = ShoppingCart::curr();
        $config = new SinglePageCheckoutComponentConfig($order);
        $form = new CheckoutForm($this->checkoutcontroller, "OrderForm", $config);
        $data = array(
            "CustomerDetails_FirstName"    => "Jane",
            "CustomerDetails_Surname"      => "Smith",
            "CustomerDetails_Email"        => "janesmith@example.com",
            "ShippingAddress_Country"      => "NZ",
            "ShippingAddress_Address"      => "1234 Green Lane",
            "ShippingAddress_AddressLine2" => "Building 2",
            "ShippingAddress_City"         => "Bleasdfweorville",
            "ShippingAddress_State"        => "Trumpo",
            "ShippingAddress_PostalCode"   => "4123",
            "ShippingAddress_Phone"        => "032092277",
            "BillingAddress_Country"       => "NZ",
            "BillingAddress_Address"       => "1234 Green Lane",
            "BillingAddress_AddressLine2"  => "Building 2",
            "BillingAddress_City"          => "Bleasdfweorville",
            "BillingAddress_State"         => "Trumpo",
            "BillingAddress_PostalCode"    => "4123",
            "BillingAddress_Phone"         => "032092277",
            "Payment_PaymentMethod"        => "Dummy",
            "Notes_Notes"                  => "Leave it around the back",
            "Terms_ReadTermsAndConditions" => "1",
        );
        $form->loadDataFrom($data, true);
        $valid = $form->validate();
        $errors = $form->getValidator()->getErrors();
        $this->assertTrue($valid, print_r($errors, true));
        $form->checkoutSubmit($data, $form);
        $this->assertEquals("Jane", $order->FirstName);
        $shipping = $order->ShippingAddress();
        $this->assertEquals("NZ", $shipping->Country);
        $this->assertEquals("Cart", $order->Status);

        $this->markTestIncomplete('test invalid data');
        $this->markTestIncomplete('test components individually');
    }

    public function testCheckoutFormForSingleCountrySiteWithReadonlyFieldsForCountry()
    {

        // Set as a single country site
        $this->loadFixture("silvershop/tests/fixtures/singlecountry.yml");
        $singlecountry = SiteConfig::current_site_config();
        $this->assertEquals(
            "NZ",
            $singlecountry->getSingleCountry(),
            "Confirm that website is setup as a single country site"
        );

        $order = ShoppingCart::curr();
        $config = new SinglePageCheckoutComponentConfig($order);
        $form = new CheckoutForm($this->checkoutcontroller, "OrderForm", $config);
        // no country fields due to readonly field
        $dataCountryAbsent = array(
            "CustomerDetailsCheckoutComponent_FirstName"    => "Jane",
            "CustomerDetailsCheckoutComponent_Surname"      => "Smith",
            "CustomerDetailsCheckoutComponent_Email"        => "janesmith@example.com",
            "ShippingAddressCheckoutComponent_Address"      => "1234 Green Lane",
            "ShippingAddressCheckoutComponent_AddressLine2" => "Building 2",
            "ShippingAddressCheckoutComponent_City"         => "Bleasdfweorville",
            "ShippingAddressCheckoutComponent_State"        => "Trumpo",
            "ShippingAddressCheckoutComponent_PostalCode"   => "4123",
            "ShippingAddressCheckoutComponent_Phone"        => "032092277",
            "BillingAddressCheckoutComponent_Address"       => "1234 Green Lane",
            "BillingAddressCheckoutComponent_AddressLine2"  => "Building 2",
            "BillingAddressCheckoutComponent_City"          => "Bleasdfweorville",
            "BillingAddressCheckoutComponent_State"         => "Trumpo",
            "BillingAddressCheckoutComponent_PostalCode"    => "4123",
            "BillingAddressCheckoutComponent_Phone"         => "032092277",
            "PaymentCheckoutComponent_PaymentMethod"        => "Dummy",
            "NotesCheckoutComponent_Notes"                  => "Leave it around the back",
            "TermsCheckoutComponent_ReadTermsAndConditions" => "1",
        );
        $form->loadDataFrom($dataCountryAbsent, true);
        $valid = $form->validate();
        $errors = $form->getValidator()->getErrors();
        $this->assertTrue($valid, print_r($errors, true));
        $this->assertTrue(
            $form->Fields()->fieldByName("ShippingAddressCheckoutComponent_Country_readonly")->isReadonly(),
            "Shipping Address Country field is readonly"
        );
        $this->assertTrue(
            $form->Fields()->fieldByName("BillingAddressCheckoutComponent_Country_readonly")->isReadonly(),
            "Billing Address Country field is readonly"
        );
        $form->checkoutSubmit($dataCountryAbsent, $form);

        $shipping = $order->ShippingAddress();
        $this->assertEquals("NZ", $shipping->Country);

        $billing = $order->BillingAddress();
        $this->assertEquals("NZ", $billing->Country);
    }
}



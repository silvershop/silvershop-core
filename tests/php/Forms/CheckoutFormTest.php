<?php

namespace SilverShop\Tests\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\SinglePageCheckoutComponentConfig;
use SilverShop\Forms\CheckoutForm;
use SilverShop\Page\CheckoutPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\ShopTest;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\SiteConfig\SiteConfig;

class CheckoutFormTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    protected Product $mp3player;
    protected Product $socks;
    protected Product $beachball;
    protected CheckoutPageController $checkoutcontroller;

    public function setUp(): void
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

        $httpRequest = new HTTPRequest('GET', '');
        $httpRequest->setSession($this->mainSession->session());
        $this->checkoutcontroller = CheckoutPageController::create();
        $this->checkoutcontroller->setRequest($httpRequest);

        ShoppingCart::singleton()->add($this->socks); //start cart
    }

    public function testCheckoutForm(): void
    {
        $order = ShoppingCart::curr();
        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create($order);
        $checkoutForm = CheckoutForm::create($this->checkoutcontroller, "OrderForm", $singlePageCheckoutComponentConfig);
        $ns = 'SilverShop-Checkout-Component-';
        $data = [
            "{$ns}CustomerDetails_FirstName"    => "Jane",
            "{$ns}CustomerDetails_Surname"      => "Smith",
            "{$ns}CustomerDetails_Email"        => "janesmith@example.com",
            "{$ns}ShippingAddress_Country"      => "NZ",
            "{$ns}ShippingAddress_Address"      => "1234 Green Lane",
            "{$ns}ShippingAddress_AddressLine2" => "Building 2",
            "{$ns}ShippingAddress_City"         => "Bleasdfweorville",
            "{$ns}ShippingAddress_State"        => "Trumpo",
            "{$ns}ShippingAddress_PostalCode"   => "4123",
            "{$ns}ShippingAddress_Phone"        => "032092277",
            "{$ns}BillingAddress_Country"       => "NZ",
            "{$ns}BillingAddress_Address"       => "1234 Green Lane",
            "{$ns}BillingAddress_AddressLine2"  => "Building 2",
            "{$ns}BillingAddress_City"          => "Bleasdfweorville",
            "{$ns}BillingAddress_State"         => "Trumpo",
            "{$ns}BillingAddress_PostalCode"    => "4123",
            "{$ns}BillingAddress_Phone"         => "032092277",
            "{$ns}Payment_PaymentMethod"        => "Dummy",
            "{$ns}Notes_Notes"                  => "Leave it around the back",
            "{$ns}Terms_ReadTermsAndConditions" => "1",
        ];
        $checkoutForm->loadDataFrom($data, true);
        $valid = $checkoutForm->validationResult()->isValid();
        $errors = $checkoutForm->getValidator()->getErrors();
        $this->assertTrue($valid, print_r($errors, true));
        $checkoutForm->checkoutSubmit($data, $checkoutForm);

        // Assert Customer Details
        $this->assertEquals("Jane", $order->FirstName);
        $this->assertEquals("Smith", $order->Surname);
        $this->assertEquals("janesmith@example.com", $order->Email);

        // Assert Shipping Address
        $address = $order->ShippingAddress();
        $this->assertEquals("NZ", $address->Country);
        $this->assertEquals("1234 Green Lane", $address->Address);
        $this->assertEquals("Building 2", $address->AddressLine2);
        $this->assertEquals("Bleasdfweorville", $address->City);
        $this->assertEquals("Trumpo", $address->State);
        $this->assertEquals("4123", $address->PostalCode);
        $this->assertEquals("032092277", $address->Phone);

        // Assert Billing Address
        $billing = $order->BillingAddress();
        $this->assertEquals("NZ", $billing->Country);
        $this->assertEquals("1234 Green Lane", $billing->Address);
        $this->assertEquals("Building 2", $billing->AddressLine2);
        $this->assertEquals("Bleasdfweorville", $billing->City);
        $this->assertEquals("Trumpo", $billing->State);
        $this->assertEquals("4123", $billing->PostalCode);
        $this->assertEquals("032092277", $billing->Phone);

        // Assert Notes
        $this->assertEquals("Leave it around the back", $order->Notes);

        // Assert Order Status
        $this->assertEquals("Cart", $order->Status);
    }

    public function testCheckoutFormForSingleCountrySiteWithReadonlyFieldsForCountry(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->AllowedCountries = '["NZ"]';
        $siteConfig->write();

        $this->assertEquals(
            "NZ",
            $siteConfig->getSingleCountry(),
            "Confirm that website is setup as a single country site"
        );

        $order = ShoppingCart::curr();
        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create($order);
        $checkoutForm = CheckoutForm::create($this->checkoutcontroller, "OrderForm", $singlePageCheckoutComponentConfig);
        $ns = 'SilverShop-Checkout-Component-';
        // no country fields due to readonly field
        $dataCountryAbsent = [
            "{$ns}CustomerDetails_FirstName"    => "Jane",
            "{$ns}CustomerDetails_Surname"      => "Smith",
            "{$ns}CustomerDetails_Email"        => "janesmith@example.com",
            "{$ns}ShippingAddress_Address"      => "1234 Green Lane",
            "{$ns}ShippingAddress_AddressLine2" => "Building 2",
            "{$ns}ShippingAddress_City"         => "Bleasdfweorville",
            "{$ns}ShippingAddress_State"        => "Trumpo",
            "{$ns}ShippingAddress_PostalCode"   => "4123",
            "{$ns}ShippingAddress_Phone"        => "032092277",
            "{$ns}BillingAddress_Address"       => "1234 Green Lane",
            "{$ns}BillingAddress_AddressLine2"  => "Building 2",
            "{$ns}BillingAddress_City"          => "Bleasdfweorville",
            "{$ns}BillingAddress_State"         => "Trumpo",
            "{$ns}BillingAddress_PostalCode"    => "4123",
            "{$ns}BillingAddress_Phone"         => "032092277",
            "{$ns}Payment_PaymentMethod"        => "Dummy",
            "{$ns}Notes_Notes"                  => "Leave it around the back",
            "{$ns}Terms_ReadTermsAndConditions" => "1",
        ];
        $checkoutForm->loadDataFrom($dataCountryAbsent, true);
        $valid = $checkoutForm->validationResult()->isValid();
        $errors = $checkoutForm->getValidator()->getErrors();
        $this->assertTrue($valid, print_r($errors, true));
        $this->assertTrue(
            $checkoutForm->Fields()->dataFieldByName("{$ns}ShippingAddress_Country_readonly")->isReadonly(),
            "Shipping Address Country field is readonly"
        );
        $this->assertTrue(
            $checkoutForm->Fields()->dataFieldByName("{$ns}BillingAddress_Country_readonly")->isReadonly(),
            "Billing Address Country field is readonly"
        );
        $checkoutForm->checkoutSubmit($dataCountryAbsent, $checkoutForm);

        $address = $order->ShippingAddress();
        $this->assertEquals("NZ", $address->Country);

        $billing = $order->BillingAddress();
        $this->assertEquals("NZ", $billing->Country);
    }

    public function testCheckoutFormWithInvalidData(): void
    {
        $order = ShoppingCart::curr();
        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create($order);
        $checkoutForm = CheckoutForm::create($this->checkoutcontroller, 'OrderForm', $singlePageCheckoutComponentConfig);
        $ns = 'SilverShop-Checkout-Component-';
        $invalidData = [
            "{$ns}CustomerDetails_FirstName"    => "",
            "{$ns}CustomerDetails_Surname"      => "",
            "{$ns}CustomerDetails_Email"        => "invalid-email",
            "{$ns}ShippingAddress_Country"      => "NZ",
            "{$ns}ShippingAddress_Address"      => "",
            "{$ns}ShippingAddress_AddressLine2" => "",
            "{$ns}ShippingAddress_City"         => "",
            "{$ns}ShippingAddress_State"        => "",
            "{$ns}ShippingAddress_PostalCode"   => "",
            "{$ns}ShippingAddress_Phone"        => "",
            "{$ns}BillingAddress_Country"       => "NZ",
            "{$ns}BillingAddress_Address"       => "",
            "{$ns}BillingAddress_AddressLine2"  => "",
            "{$ns}BillingAddress_City"          => "",
            "{$ns}BillingAddress_State"         => "",
            "{$ns}BillingAddress_PostalCode"    => "",
            "{$ns}BillingAddress_Phone"         => "",
            "{$ns}Payment_PaymentMethod"        => "",
            "{$ns}Notes_Notes"                  => "",
            "{$ns}Terms_ReadTermsAndConditions" => "",
        ];
        $checkoutForm->loadDataFrom($invalidData, true);
        $valid = $checkoutForm->validationResult()->isValid();
        $errors = $checkoutForm->getValidator()->getErrors();
        $this->assertFalse($valid, 'Form should be invalid with empty and incorrect data');
        $this->assertNotEmpty($errors, 'There should be validation errors');
    }
}

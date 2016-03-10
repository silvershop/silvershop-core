<?php

class CheckoutFormTest extends SapphireTest
{
    public static $fixture_file = 'silvershop/tests/fixtures/shop.yml';

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->mp3player = $this->objFromFixture('Product', 'mp3player');
        $this->mp3player->publish('Stage', 'Live');
        $this->socks = $this->objFromFixture('Product', 'socks');
        $this->socks->publish('Stage', 'Live');
        $this->beachball = $this->objFromFixture('Product', 'beachball');
        $this->beachball->publish('Stage', 'Live');

        $this->checkoutcontroller = new CheckoutPage_Controller();

        ShoppingCart::singleton()->add($this->socks); //start cart
    }

    public function testCheckoutForm()
    {
        $order = ShoppingCart::curr();
        $config = new SinglePageCheckoutComponentConfig($order);
        $form = new CheckoutForm($this->checkoutcontroller, "OrderForm", $config);
        $data = array(
            "CustomerDetailsCheckoutComponent_FirstName"    => "Jane",
            "CustomerDetailsCheckoutComponent_Surname"      => "Smith",
            "CustomerDetailsCheckoutComponent_Email"        => "janesmith@example.com",
            "ShippingAddressCheckoutComponent_Country"      => "NZ",
            "ShippingAddressCheckoutComponent_Address"      => "1234 Green Lane",
            "ShippingAddressCheckoutComponent_AddressLine2" => "Building 2",
            "ShippingAddressCheckoutComponent_City"         => "Bleasdfweorville",
            "ShippingAddressCheckoutComponent_State"        => "Trumpo",
            "ShippingAddressCheckoutComponent_PostalCode"   => "4123",
            "ShippingAddressCheckoutComponent_Phone"        => "032092277",
            "BillingAddressCheckoutComponent_Country"       => "NZ",
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



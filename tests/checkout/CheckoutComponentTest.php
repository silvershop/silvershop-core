<?php

class CheckoutComponentTest extends SapphireTest
{
    protected static $fixture_file = array(
        'silvershop/tests/fixtures/Orders.yml',
        'silvershop/tests/fixtures/Addresses.yml',
        'silvershop/tests/fixtures/shop.yml',
        'silvershop/tests/fixtures/ShopMembers.yml',
    );

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->cart = $this->objFromFixture("Order", "cart1");
        $this->address1 = $this->objFromFixture("Address", "address1");
        $this->address2 = $this->objFromFixture("Address", "address2");
        $this->addressNoCountry = $this->objFromFixture("Address", "pukekohe");
        CheckoutConfig::config()->member_creation_enabled = true;
        CheckoutConfig::config()->membership_required = false;
    }

    public function testSinglePageConfig()
    {
        $order = new Order();  //start a new order
        $order->write();
        $config = new SinglePageCheckoutComponentConfig($order);

        $customerdetailscomponent = $config->getComponentByType("CustomerDetailsCheckoutComponent");
        $customerdetailscomponent->setData(
            $order,
            array(
                "FirstName" => "Ed",
                "Surname"   => "Hillary",
                "Email"     => "ed@everest.net.xx",
            )
        );

        $shippingaddresscomponent = $config->getComponentByType("ShippingAddressCheckoutComponent");
        $shippingaddresscomponent->setData($order, $this->address1->toMap());

        $billingaddresscomponent = $config->getComponentByType("BillingAddressCheckoutComponent");
        $billingaddresscomponent->setData($order, $this->address2->toMap());

        $paymentcomponent = $config->getComponentByType("PaymentCheckoutComponent");
        $paymentcomponent->setData(
            $order,
            array(
                "PaymentMethod" => "Dummy",
            )
        );

        $notescomponent = $config->getComponentByType("NotesCheckoutComponent");
        $notescomponent->setData(
            $order,
            array(
                "Notes" => "Please bring coffee with goods",
            )
        );

        $termscomponent = $config->getComponentByType("TermsCheckoutComponent");
        $termscomponent->setData(
            $order,
            array(
                "ReadTermsAndConditions" => true,
            )
        );

        $components = $config->getComponents();
        $this->assertContainsOnlyInstancesOf(
            "CheckoutComponent_Namespaced",
            $components,
            "Name of ArrayList is CheckoutComponent_Namespaced"
        );
        $this->assertContains("CustomerDetailsCheckoutComponent", print_r($components, true));
        $this->assertContains("ShippingAddressCheckoutComponent", print_r($components, true));
        $this->assertContains("BillingAddressCheckoutComponent", print_r($components, true));
        $this->assertContains("PaymentCheckoutComponent", print_r($components, true));
        $this->assertContains("NotesCheckoutComponent", print_r($components, true));
        $this->assertContains("TermsCheckoutComponent", print_r($components, true));

        $fields = $config->getFormFields();

        $this->assertContains(
            "CustomerDetailsCheckoutComponent_FirstName",
            print_r($fields, true),
            "Form Fields should contain a CustomerDetailsCheckoutComponent_FirstName field"
        );
        $this->assertContains(
            "CustomerDetailsCheckoutComponent_Surname",
            print_r($fields, true),
            "Form Fields should contain a CustomerDetailsCheckoutComponent_Surname field"
        );
        $this->assertContains(
            "CustomerDetailsCheckoutComponent_Email",
            print_r($fields, true),
            "Form Fields should contain a CustomerDetailsCheckoutComponent_Email field"
        );
        $this->assertContains(
            "ShippingAddressCheckoutComponent_Country",
            print_r($fields, true),
            "Form Fields should contain a ShippingAddressCheckoutComponent_Country field"
        );
        $this->assertContains(
            "ShippingAddressCheckoutComponent_Address",
            print_r($fields, true),
            "Form Fields should contain a ShippingAddressCheckoutComponent_Address field"
        );
        $this->assertContains(
            "ShippingAddressCheckoutComponent_City",
            print_r($fields, true),
            "Form Fields should contain a ShippingAddressCheckoutComponent_City field"
        );
        $this->assertContains(
            "ShippingAddressCheckoutComponent_State",
            print_r($fields, true),
            "Form Fields should contain a ShippingAddressCheckoutComponent_State field"
        );
        $this->assertContains(
            "BillingAddressCheckoutComponent_Country",
            print_r($fields, true),
            "Form Fields should contain a BillingAddressCheckoutComponent_Country field"
        );
        $this->assertContains(
            "BillingAddressCheckoutComponent_Address",
            print_r($fields, true),
            "Form Fields should contain a BillingAddressCheckoutComponent_Address field"
        );
        $this->assertContains(
            "BillingAddressCheckoutComponent_City",
            print_r($fields, true),
            "Form Fields should contain a BillingAddressCheckoutComponent_City field"
        );
        $this->assertContains(
            "BillingAddressCheckoutComponent_State",
            print_r($fields, true),
            "Form Fields should contain a BillingAddressCheckoutComponent_State field"
        );
        $this->assertNotContains("rubbish", print_r($fields, true), "Form Field should not include 'rubbish'");

        $required = $config->getRequiredFields();
        $requiredfields = array(
            "CustomerDetailsCheckoutComponent_FirstName",
            "CustomerDetailsCheckoutComponent_Surname",
            "CustomerDetailsCheckoutComponent_Email",
            "ShippingAddressCheckoutComponent_Country",
            "ShippingAddressCheckoutComponent_State",
            "ShippingAddressCheckoutComponent_City",
            "ShippingAddressCheckoutComponent_Address",
            "BillingAddressCheckoutComponent_Country",
            "BillingAddressCheckoutComponent_State",
            "BillingAddressCheckoutComponent_City",
            "BillingAddressCheckoutComponent_Address",
        );
        $this->assertSame(
            $requiredfields,
            $required,
            "getRequiredFields function returns required fields from numerous components"
        );

        $data = $config->getData();

        $this->assertEquals("Ed", $data["CustomerDetailsCheckoutComponent_FirstName"]);
        $this->assertEquals("Hillary", $data["CustomerDetailsCheckoutComponent_Surname"]);
        $this->assertEquals("ed@everest.net.xx", $data["CustomerDetailsCheckoutComponent_Email"]);
        $this->assertEquals("AU", $data["ShippingAddressCheckoutComponent_Country"]);
        $this->assertEquals("South Australia", $data["ShippingAddressCheckoutComponent_State"]);
        $this->assertEquals("WEST BEACH", $data["ShippingAddressCheckoutComponent_City"]);
        $this->assertEquals("5024", $data["ShippingAddressCheckoutComponent_PostalCode"]);
        $this->assertEquals("201-203 BROADWAY AVE", $data["ShippingAddressCheckoutComponent_Address"]);
        $this->assertEquals("U 235", $data["ShippingAddressCheckoutComponent_AddressLine2"]);
        $this->assertEquals("NZ", $data["BillingAddressCheckoutComponent_Country"]);
        $this->assertEquals("Ipsum", $data["BillingAddressCheckoutComponent_State"]);
        $this->assertEquals("Lorem", $data["BillingAddressCheckoutComponent_City"]);
        $this->assertEquals("1234", $data["BillingAddressCheckoutComponent_PostalCode"]);
        $this->assertEquals("2 Foobar Ave", $data["BillingAddressCheckoutComponent_Address"]);
        $this->assertEquals("U 235", $data["BillingAddressCheckoutComponent_AddressLine2"]);
        $this->assertEquals("Dummy", $data["PaymentCheckoutComponent_PaymentMethod"]);
        $this->assertEquals("Please bring coffee with goods", $data["NotesCheckoutComponent_Notes"]);

        $validateData = $config->validateData($data);
        $this->assertTrue(
            $validateData,
            print_r($validateData, true),
            "Data validation must return true" . print_r($validateData, true)
        );

        $config->setData($data);
        //assertions!

        //form field generation
        //validate data
        //set data
        //get data
        $this->markTestIncomplete('Lots missing here');
    }

    public function testSinglePageConfigForSingleCountrySiteWithReadonlyFieldsForCountry()
    {
        // Set as a single country site
        $this->loadFixture("silvershop/tests/fixtures/singlecountry.yml");
        $singlecountry = SiteConfig::current_site_config();
        $this->assertEquals(
            "NZ",
            $singlecountry->getSingleCountry(),
            "Confirm that website is setup as a single country site"
        );

        $order = new Order();  //start a new order
        $order->write();
        $config = new SinglePageCheckoutComponentConfig($order);

        $customerdetailscomponent = $config->getComponentByType("CustomerDetailsCheckoutComponent");
        $customerdetailscomponent->setData(
            $order,
            array(
                "FirstName" => "John",
                "Surname"   => "Walker",
                "Email"     => "jw@onehundedsubfourminutemiles.nz",
            )
        );

        $shippingaddresscomponent = $config->getComponentByType("ShippingAddressCheckoutComponent");
        $shippingaddresscomponent->setData($order, $this->addressNoCountry->toMap());

        $billingaddresscomponent = $config->getComponentByType("BillingAddressCheckoutComponent");
        $billingaddresscomponent->setData($order, $this->addressNoCountry->toMap());

        $paymentcomponent = $config->getComponentByType("PaymentCheckoutComponent");
        $paymentcomponent->setData(
            $order,
            array(
                "PaymentMethod" => "Dummy",
            )
        );

        $fields = $config->getFormFields();
        $shippingaddressfield = $fields->fieldByName("ShippingAddressCheckoutComponent_Country_readonly");
        $billingaddressfield = $fields->fieldByName("BillingAddressCheckoutComponent_Country_readonly");

        $this->assertContains(
            "New Zealand",
            $shippingaddressfield->Value(),
            "The value of the Shipping Country readonly field is 'New Zealand'"
        );
        $this->assertContains(
            "New Zealand",
            $billingaddressfield->Value(),
            "The value of the Billing Country readonly field is 'New Zealand'"
        );
        $this->assertTrue($shippingaddressfield->isReadonly(), "The Shipping Address Country field is readonly");
        $this->assertTrue($shippingaddressfield->isReadonly(), "The Billing Address Country field is readonly");

        $required = $config->getRequiredFields();
        $requiredfieldswithCountryAbsent = array(
            "CustomerDetailsCheckoutComponent_FirstName",
            "CustomerDetailsCheckoutComponent_Surname",
            "CustomerDetailsCheckoutComponent_Email",
            "ShippingAddressCheckoutComponent_State",
            "ShippingAddressCheckoutComponent_City",
            "ShippingAddressCheckoutComponent_Address",
            "BillingAddressCheckoutComponent_State",
            "BillingAddressCheckoutComponent_City",
            "BillingAddressCheckoutComponent_Address",
        );
        $this->assertSame(
            $requiredfieldswithCountryAbsent,
            $required,
            "getRequiredFields function returns required fields from numerous components except for the Country fields (no need to validate readonly fields)"
        );

        $data = $config->getData();
        $this->assertEquals("NZ", $data["ShippingAddressCheckoutComponent_Country"]);
        $this->assertEquals("NZ", $data["BillingAddressCheckoutComponent_Country"]);

        $validateData = $config->validateData($data);
        $this->assertTrue(
            $validateData,
            print_r($validateData, true),
            "Data validation must return true.  Note: should not be testing a country field here as validation of a readonly field is not necessary"
            . print_r($validateData, true)
        );

        $config->setData($data);
    }
}

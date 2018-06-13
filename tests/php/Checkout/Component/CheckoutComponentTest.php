<?php

namespace SilverShop\Tests\Checkout\Component;

use SilverShop\Checkout\CheckoutConfig;
use SilverShop\Checkout\Component\BillingAddress;
use SilverShop\Checkout\Component\CheckoutComponentNamespaced;
use SilverShop\Checkout\Component\CustomerDetails;
use SilverShop\Checkout\Component\Notes;
use SilverShop\Checkout\Component\Payment;
use SilverShop\Checkout\Component\ShippingAddress;
use SilverShop\Checkout\Component\Terms;
use SilverShop\Checkout\SinglePageCheckoutComponentConfig;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverShop\Tests\ShopTest;
use SilverStripe\Core\Config\Config;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Dev\SapphireTest;

class CheckoutComponentTest extends SapphireTest
{
    protected static $fixture_file = array(
        __DIR__ . '/../../Fixtures/Orders.yml',
        __DIR__ . '/../../Fixtures/Addresses.yml',
        __DIR__ . '/../../Fixtures/shop.yml',
        __DIR__ . '/../../Fixtures/ShopMembers.yml',
    );

    /**
     * @var Order
     */
    protected $cart;

    /**
     * @var Address
     */
    protected $address1;

    /**
     * @var Address
     */
    protected $address2;

    /**
     * @var Address
     */
    protected $addressNoCountry;

    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->cart = $this->objFromFixture(Order::class, "cart1");
        $this->address1 = $this->objFromFixture(Address::class, "address1");
        $this->address2 = $this->objFromFixture(Address::class, "address2");
        $this->addressNoCountry = $this->objFromFixture(Address::class, "pukekohe");

        Config::modify()
            ->set(CheckoutConfig::class, 'member_creation_enabled', true)
            ->set(CheckoutConfig::class, 'membership_required', false);
    }

    public function testSinglePageConfig()
    {
        $order = new Order();  //start a new order
        $order->write();
        $config = new SinglePageCheckoutComponentConfig($order);

        $customerdetailscomponent = $config->getComponentByType(CustomerDetails::class);
        $customerdetailscomponent->setData(
            $order,
            array(
                "FirstName" => "Ed",
                "Surname"   => "Hillary",
                "Email"     => "ed@example.com",
            )
        );

        $shippingaddresscomponent = $config->getComponentByType(ShippingAddress::class);
        $shippingaddresscomponent->setData($order, $this->address1->toMap());

        $billingaddresscomponent = $config->getComponentByType(BillingAddress::class);
        $billingaddresscomponent->setData($order, $this->address2->toMap());

        $paymentcomponent = $config->getComponentByType(Payment::class);
        $paymentcomponent->setData(
            $order,
            array(
                "PaymentMethod" => "Dummy",
            )
        );

        $notescomponent = $config->getComponentByType(Notes::class);
        $notescomponent->setData(
            $order,
            array(
                "Notes" => "Please bring coffee with goods",
            )
        );

        $termscomponent = $config->getComponentByType(Terms::class);
        $termscomponent->setData(
            $order,
            array(
                "ReadTermsAndConditions" => true,
            )
        );

        $components = $config->getComponents();
        $class = CheckoutComponentNamespaced::class;
        $this->assertContainsOnlyInstancesOf(
            $class,
            $components,
            "Components must only be of type '$class'"
        );
        $this->assertContains(CustomerDetails::class, print_r($components, true));
        $this->assertContains(ShippingAddress::class, print_r($components, true));
        $this->assertContains(BillingAddress::class, print_r($components, true));
        $this->assertContains(Payment::class, print_r($components, true));
        $this->assertContains(Notes::class, print_r($components, true));
        $this->assertContains(Terms::class, print_r($components, true));

        $fields = $config->getFormFields();

        $ns = 'SilverShop-Checkout-Component';
        $this->assertContains(
            "$ns-CustomerDetails_FirstName",
            print_r($fields, true),
            "Form Fields should contain a $ns-CustomerDetails_FirstName field"
        );
        $this->assertContains(
            "$ns-CustomerDetails_Surname",
            print_r($fields, true),
            "Form Fields should contain a $ns-CustomerDetails_Surname field"
        );
        $this->assertContains(
            "$ns-CustomerDetails_Email",
            print_r($fields, true),
            "Form Fields should contain a $ns-CustomerDetails_Email field"
        );
        $this->assertContains(
            "$ns-ShippingAddress_Country",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_Country field"
        );
        $this->assertContains(
            "$ns-ShippingAddress_Address",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_Address field"
        );
        $this->assertContains(
            "$ns-ShippingAddress_City",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_City field"
        );
        $this->assertContains(
            "$ns-ShippingAddress_State",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_State field"
        );
        $this->assertContains(
            "$ns-BillingAddress_Country",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_Country field"
        );
        $this->assertContains(
            "$ns-BillingAddress_Address",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_Address field"
        );
        $this->assertContains(
            "$ns-BillingAddress_City",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_City field"
        );
        $this->assertContains(
            "$ns-BillingAddress_State",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_State field"
        );
        $this->assertNotContains("rubbish", print_r($fields, true), "Form Field should not include 'rubbish'");

        $required = $config->getRequiredFields();
        $requiredfields = array(
            "$ns-CustomerDetails_FirstName",
            "$ns-CustomerDetails_Surname",
            "$ns-CustomerDetails_Email",
            "$ns-ShippingAddress_Country",
            "$ns-ShippingAddress_State",
            "$ns-ShippingAddress_City",
            "$ns-ShippingAddress_Address",
            "$ns-BillingAddress_Country",
            "$ns-BillingAddress_State",
            "$ns-BillingAddress_City",
            "$ns-BillingAddress_Address",
        );
        $this->assertSame(
            $requiredfields,
            $required,
            "getRequiredFields function returns required fields from numerous components"
        );

        $data = $config->getData();

        $this->assertEquals("Ed", $data["$ns-CustomerDetails_FirstName"]);
        $this->assertEquals("Hillary", $data["$ns-CustomerDetails_Surname"]);
        $this->assertEquals("ed@example.com", $data["$ns-CustomerDetails_Email"]);
        $this->assertEquals("AU", $data["$ns-ShippingAddress_Country"]);
        $this->assertEquals("South Australia", $data["$ns-ShippingAddress_State"]);
        $this->assertEquals("WEST BEACH", $data["$ns-ShippingAddress_City"]);
        $this->assertEquals("5024", $data["$ns-ShippingAddress_PostalCode"]);
        $this->assertEquals("201-203 BROADWAY AVE", $data["$ns-ShippingAddress_Address"]);
        $this->assertEquals("U 235", $data["$ns-ShippingAddress_AddressLine2"]);
        $this->assertEquals("NZ", $data["$ns-BillingAddress_Country"]);
        $this->assertEquals("Ipsum", $data["$ns-BillingAddress_State"]);
        $this->assertEquals("Lorem", $data["$ns-BillingAddress_City"]);
        $this->assertEquals("1234", $data["$ns-BillingAddress_PostalCode"]);
        $this->assertEquals("2 Foobar Ave", $data["$ns-BillingAddress_Address"]);
        $this->assertEquals("U 235", $data["$ns-BillingAddress_AddressLine2"]);
        $this->assertEquals("Dummy", $data["$ns-Payment_PaymentMethod"]);
        $this->assertEquals("Please bring coffee with goods", $data["$ns-Notes_Notes"]);

        $validateData = $config->validateData($data);
        $this->assertTrue(
            $validateData,
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
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->AllowedCountries = '["NZ"]';
        $siteConfig->write();

        $this->assertEquals(
            "NZ",
            $siteConfig->getSingleCountry(),
            "Confirm that website is setup as a single country site"
        );

        $order = new Order();  //start a new order
        $order->write();
        $config = new SinglePageCheckoutComponentConfig($order);

        $customerdetailscomponent = $config->getComponentByType(CustomerDetails::class);
        $customerdetailscomponent->setData(
            $order,
            array(
                "FirstName" => "John",
                "Surname"   => "Walker",
                "Email"     => "jw@example.com",
            )
        );

        $shippingaddresscomponent = $config->getComponentByType(ShippingAddress::class);
        $shippingaddresscomponent->setData($order, $this->addressNoCountry->toMap());

        $billingaddresscomponent = $config->getComponentByType(BillingAddress::class);
        $billingaddresscomponent->setData($order, $this->addressNoCountry->toMap());

        $paymentcomponent = $config->getComponentByType(Payment::class);
        $paymentcomponent->setData(
            $order,
            array(
                "PaymentMethod" => "Dummy",
            )
        );

        $fields = $config->getFormFields();
        $ns = 'SilverShop-Checkout-Component';
        $shippingaddressfield = $fields->fieldByName("$ns-ShippingAddress_Country_readonly");
        $billingaddressfield = $fields->fieldByName("$ns-BillingAddress_Country_readonly");

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
            "$ns-CustomerDetails_FirstName",
            "$ns-CustomerDetails_Surname",
            "$ns-CustomerDetails_Email",
            "$ns-ShippingAddress_State",
            "$ns-ShippingAddress_City",
            "$ns-ShippingAddress_Address",
            "$ns-BillingAddress_State",
            "$ns-BillingAddress_City",
            "$ns-BillingAddress_Address",
        );
        $this->assertSame(
            $requiredfieldswithCountryAbsent,
            $required,
            "getRequiredFields function returns required fields from numerous components except for the Country fields (no need to validate readonly fields)"
        );

        $data = $config->getData();
        $this->assertEquals("NZ", $data["$ns-ShippingAddress_Country"]);
        $this->assertEquals("NZ", $data["$ns-BillingAddress_Country"]);

        $validateData = $config->validateData($data);
        $this->assertTrue(
            $validateData,
            "Data validation must return true.  Note: should not be testing a country field here as validation of a readonly field is not necessary"
            . print_r($validateData, true)
        );

        $config->setData($data);
    }
}

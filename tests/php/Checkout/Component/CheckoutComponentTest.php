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
    protected static $fixture_file = [
        __DIR__ . '/../../Fixtures/Orders.yml',
        __DIR__ . '/../../Fixtures/Addresses.yml',
        __DIR__ . '/../../Fixtures/shop.yml',
        __DIR__ . '/../../Fixtures/ShopMembers.yml',
    ];

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

    public function setUp(): void
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

    public function testSinglePageConfig(): void
    {
        $order = Order::create();  //start a new order
        $order->write();
        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create($order);

        $customerdetailscomponent = $singlePageCheckoutComponentConfig->getComponentByType(CustomerDetails::class);
        $customerdetailscomponent->setData(
            $order,
            [
                "FirstName" => "Ed",
                "Surname"   => "Hillary",
                "Email"     => "ed@example.com",
            ]
        );

        $shippingaddresscomponent = $singlePageCheckoutComponentConfig->getComponentByType(ShippingAddress::class);
        $shippingaddresscomponent->setData($order, $this->address1->toMap());

        $billingaddresscomponent = $singlePageCheckoutComponentConfig->getComponentByType(BillingAddress::class);
        $billingaddresscomponent->setData($order, $this->address2->toMap());

        $paymentcomponent = $singlePageCheckoutComponentConfig->getComponentByType(Payment::class);
        $paymentcomponent->setData(
            $order,
            [
                "PaymentMethod" => "Dummy",
            ]
        );

        $notescomponent = $singlePageCheckoutComponentConfig->getComponentByType(Notes::class);
        $notescomponent->setData(
            $order,
            [
                "Notes" => "Please bring coffee with goods",
            ]
        );

        $termscomponent = $singlePageCheckoutComponentConfig->getComponentByType(Terms::class);
        $termscomponent->setData(
            $order,
            [
                "ReadTermsAndConditions" => true,
            ]
        );

        $arrayList = $singlePageCheckoutComponentConfig->getComponents();
        $class = CheckoutComponentNamespaced::class;
        $this->assertContainsOnlyInstancesOf(
            $class,
            $arrayList,
            "Components must only be of type '$class'"
        );
        $this->assertStringContainsString(CustomerDetails::class, print_r($arrayList, true));
        $this->assertStringContainsString(ShippingAddress::class, print_r($arrayList, true));
        $this->assertStringContainsString(BillingAddress::class, print_r($arrayList, true));
        $this->assertStringContainsString(Payment::class, print_r($arrayList, true));
        $this->assertStringContainsString(Notes::class, print_r($arrayList, true));
        $this->assertStringContainsString(Terms::class, print_r($arrayList, true));

        $fields = $singlePageCheckoutComponentConfig->getFormFields();

        $ns = 'SilverShop-Checkout-Component';
        $this->assertStringContainsString(
            "$ns-CustomerDetails_FirstName",
            print_r($fields, true),
            "Form Fields should contain a $ns-CustomerDetails_FirstName field"
        );
        $this->assertStringContainsString(
            "$ns-CustomerDetails_Surname",
            print_r($fields, true),
            "Form Fields should contain a $ns-CustomerDetails_Surname field"
        );
        $this->assertStringContainsString(
            "$ns-CustomerDetails_Email",
            print_r($fields, true),
            "Form Fields should contain a $ns-CustomerDetails_Email field"
        );
        $this->assertStringContainsString(
            "$ns-ShippingAddress_Country",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_Country field"
        );
        $this->assertStringContainsString(
            "$ns-ShippingAddress_Address",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_Address field"
        );
        $this->assertStringContainsString(
            "$ns-ShippingAddress_City",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_City field"
        );
        $this->assertStringContainsString(
            "$ns-ShippingAddress_State",
            print_r($fields, true),
            "Form Fields should contain a $ns-ShippingAddress_State field"
        );
        $this->assertStringContainsString(
            "$ns-BillingAddress_Country",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_Country field"
        );
        $this->assertStringContainsString(
            "$ns-BillingAddress_Address",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_Address field"
        );
        $this->assertStringContainsString(
            "$ns-BillingAddress_City",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_City field"
        );
        $this->assertStringContainsString(
            "$ns-BillingAddress_State",
            print_r($fields, true),
            "Form Fields should contain a $ns-BillingAddress_State field"
        );
        $this->assertStringNotContainsString("rubbish", print_r($fields, true), "Form Field should not include 'rubbish'");

        $required = $singlePageCheckoutComponentConfig->getRequiredFields();
        $requiredfields = [
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
        ];
        $this->assertSame(
            $requiredfields,
            $required,
            "getRequiredFields function returns required fields from numerous components"
        );

        $data = $singlePageCheckoutComponentConfig->getData();

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

        $validateData = $singlePageCheckoutComponentConfig->validateData($data);
        $this->assertTrue(
            $validateData,
            "Data validation must return true" . print_r($validateData, true)
        );

        $singlePageCheckoutComponentConfig->setData($data);
        $fields = $singlePageCheckoutComponentConfig->getFormFields();
        $this->assertNotNull($fields, "Form should be generated");

        // Test required fields validation
        $this->assertEquals(
            11,
            count($singlePageCheckoutComponentConfig->getRequiredFields()),
            'Component should have required fields'
        );
        $this->assertTrue(
            in_array("$ns-CustomerDetails_Email", $singlePageCheckoutComponentConfig->getRequiredFields()),
            'Email is a required field'
        );
    }

    public function testSinglePageConfigForSingleCountrySiteWithReadonlyFieldsForCountry(): void
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

        $order = Order::create();  //start a new order
        $order->write();
        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create($order);

        $customerdetailscomponent = $singlePageCheckoutComponentConfig->getComponentByType(CustomerDetails::class);
        $customerdetailscomponent->setData(
            $order,
            [
                "FirstName" => "John",
                "Surname"   => "Walker",
                "Email"     => "jw@example.com",
            ]
        );

        $shippingaddresscomponent = $singlePageCheckoutComponentConfig->getComponentByType(ShippingAddress::class);
        $shippingaddresscomponent->setData($order, $this->addressNoCountry->toMap());

        $billingaddresscomponent = $singlePageCheckoutComponentConfig->getComponentByType(BillingAddress::class);
        $billingaddresscomponent->setData($order, $this->addressNoCountry->toMap());

        $paymentcomponent = $singlePageCheckoutComponentConfig->getComponentByType(Payment::class);
        $paymentcomponent->setData(
            $order,
            [
                "PaymentMethod" => "Dummy",
            ]
        );

        $fieldList = $singlePageCheckoutComponentConfig->getFormFields();
        $ns = 'SilverShop-Checkout-Component';
        $shippingaddressfield = $fieldList->dataFieldByName("$ns-ShippingAddress_Country_readonly");
        $billingaddressfield = $fieldList->dataFieldByName("$ns-BillingAddress_Country_readonly");

        $this->assertStringContainsString(
            "New Zealand",
            $shippingaddressfield->Value(),
            "The value of the Shipping Country readonly field is 'New Zealand'"
        );
        $this->assertStringContainsString(
            "New Zealand",
            $billingaddressfield->Value(),
            "The value of the Billing Country readonly field is 'New Zealand'"
        );
        $this->assertTrue($shippingaddressfield->isReadonly(), "The Shipping Address Country field is readonly");
        $this->assertTrue($shippingaddressfield->isReadonly(), "The Billing Address Country field is readonly");

        $required = $singlePageCheckoutComponentConfig->getRequiredFields();
        $requiredfieldswithCountryAbsent = [
            "$ns-CustomerDetails_FirstName",
            "$ns-CustomerDetails_Surname",
            "$ns-CustomerDetails_Email",
            "$ns-ShippingAddress_State",
            "$ns-ShippingAddress_City",
            "$ns-ShippingAddress_Address",
            "$ns-BillingAddress_State",
            "$ns-BillingAddress_City",
            "$ns-BillingAddress_Address",
        ];
        $this->assertSame(
            $requiredfieldswithCountryAbsent,
            $required,
            "getRequiredFields function returns required fields from numerous components except for the Country fields (no need to validate readonly fields)"
        );

        $data = $singlePageCheckoutComponentConfig->getData();
        $this->assertEquals("NZ", $data["$ns-ShippingAddress_Country"]);
        $this->assertEquals("NZ", $data["$ns-BillingAddress_Country"]);

        $validateData = $singlePageCheckoutComponentConfig->validateData($data);
        $this->assertTrue(
            $validateData,
            "Data validation must return true.  Note: should not be testing a country field here as validation of a readonly field is not necessary"
            . print_r($validateData, true)
        );

        $singlePageCheckoutComponentConfig->setData($data);
    }
}

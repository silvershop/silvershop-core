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
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

class CheckoutFormTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    /**
     * @var Product
     */
    protected $mp3player;

    /**
     * @var Product
     */
    protected $socks;

    /**
     * @var Product
     */
    protected $beachball;

    /**
     * @var CheckoutPageController
     */
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

        $request = new HTTPRequest('GET', '');
        $request->setSession($this->mainSession->session());
        $this->checkoutcontroller = new CheckoutPageController();
        $this->checkoutcontroller->setRequest($request);

        ShoppingCart::singleton()->add($this->socks); //start cart
    }

    public function testCheckoutForm()
    {
        $order = ShoppingCart::curr();
        $config = new SinglePageCheckoutComponentConfig($order);
        $form = new CheckoutForm($this->checkoutcontroller, "OrderForm", $config);
        $ns = 'SilverShop-Checkout-Component-';
        $data = array(
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
        );
        $form->loadDataFrom($data, true);
        $valid = $form->validationResult()->isValid();
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
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->AllowedCountries = '["NZ"]';
        $siteConfig->write();

        $this->assertEquals(
            "NZ",
            $siteConfig->getSingleCountry(),
            "Confirm that website is setup as a single country site"
        );

        $order = ShoppingCart::curr();
        $config = new SinglePageCheckoutComponentConfig($order);
        $form = new CheckoutForm($this->checkoutcontroller, "OrderForm", $config);
        $ns = 'SilverShop-Checkout-Component-';
        // no country fields due to readonly field
        $dataCountryAbsent = array(
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
        );
        $form->loadDataFrom($dataCountryAbsent, true);
        $valid = $form->validationResult()->isValid();
        $errors = $form->getValidator()->getErrors();
        $this->assertTrue($valid, print_r($errors, true));
        $this->assertTrue(
            $form->Fields()->fieldByName("{$ns}ShippingAddress_Country_readonly")->isReadonly(),
            "Shipping Address Country field is readonly"
        );
        $this->assertTrue(
            $form->Fields()->fieldByName("{$ns}BillingAddress_Country_readonly")->isReadonly(),
            "Billing Address Country field is readonly"
        );
        $form->checkoutSubmit($dataCountryAbsent, $form);

        $shipping = $order->ShippingAddress();
        $this->assertEquals("NZ", $shipping->Country);

        $billing = $order->BillingAddress();
        $this->assertEquals("NZ", $billing->Country);
    }
}

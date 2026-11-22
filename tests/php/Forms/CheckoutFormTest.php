<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\SinglePageCheckoutComponentConfig;
use SilverShop\Forms\CheckoutForm;
use SilverShop\Model\Address;
use SilverShop\Page\CheckoutPageController;
use SilverShop\Page\Product;
use SilverShop\Tests\Checkout\Component\ErrorReportingComponent;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

final class CheckoutFormTest extends FunctionalTest
{
    public static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    protected Product $mp3player;

    protected Product $socks;

    protected Product $beachball;

    protected CheckoutPageController $checkoutcontroller;

    protected function setUp(): void
    {
        parent::setUp();
        ShoppingCart::singleton()->clear();
        ShopTestBootstrap::setConfiguration();
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
            $ns . 'CustomerDetails_FirstName'    => "Jane",
            $ns . 'CustomerDetails_Surname'      => "Smith",
            $ns . 'CustomerDetails_Email'        => "janesmith@example.com",
            $ns . 'ShippingAddress_Country'      => "NZ",
            $ns . 'ShippingAddress_Address'      => "1234 Green Lane",
            $ns . 'ShippingAddress_AddressLine2' => "Building 2",
            $ns . 'ShippingAddress_City'         => "Bleasdfweorville",
            $ns . 'ShippingAddress_State'        => "Trumpo",
            $ns . 'ShippingAddress_PostalCode'   => "4123",
            $ns . 'ShippingAddress_Phone'        => "032092277",
            $ns . 'BillingAddress_Country'       => "NZ",
            $ns . 'BillingAddress_Address'       => "1234 Green Lane",
            $ns . 'BillingAddress_AddressLine2'  => "Building 2",
            $ns . 'BillingAddress_City'          => "Bleasdfweorville",
            $ns . 'BillingAddress_State'         => "Trumpo",
            $ns . 'BillingAddress_PostalCode'    => "4123",
            $ns . 'BillingAddress_Phone'         => "032092277",
            $ns . 'Payment_PaymentMethod'        => "Dummy",
            $ns . 'Notes_Notes'                  => "Leave it around the back",
            $ns . 'Terms_ReadTermsAndConditions' => "1",
        ];
        $checkoutForm->loadDataFrom($data, 1);
        $valid = $checkoutForm->validate()->isValid();
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
            $ns . 'CustomerDetails_FirstName'    => "Jane",
            $ns . 'CustomerDetails_Surname'      => "Smith",
            $ns . 'CustomerDetails_Email'        => "janesmith@example.com",
            $ns . 'ShippingAddress_Address'      => "1234 Green Lane",
            $ns . 'ShippingAddress_AddressLine2' => "Building 2",
            $ns . 'ShippingAddress_City'         => "Bleasdfweorville",
            $ns . 'ShippingAddress_State'        => "Trumpo",
            $ns . 'ShippingAddress_PostalCode'   => "4123",
            $ns . 'ShippingAddress_Phone'        => "032092277",
            $ns . 'BillingAddress_Address'       => "1234 Green Lane",
            $ns . 'BillingAddress_AddressLine2'  => "Building 2",
            $ns . 'BillingAddress_City'          => "Bleasdfweorville",
            $ns . 'BillingAddress_State'         => "Trumpo",
            $ns . 'BillingAddress_PostalCode'    => "4123",
            $ns . 'BillingAddress_Phone'         => "032092277",
            $ns . 'Payment_PaymentMethod'        => "Dummy",
            $ns . 'Notes_Notes'                  => "Leave it around the back",
            $ns . 'Terms_ReadTermsAndConditions' => "1",
        ];
        $checkoutForm->loadDataFrom($dataCountryAbsent, 1);
        $valid = $checkoutForm->validate()->isValid();
        $errors = $checkoutForm->getValidator()->getErrors();
        $this->assertTrue($valid, print_r($errors, true));
        $this->assertTrue(
            $checkoutForm->Fields()->dataFieldByName($ns . 'ShippingAddress_Country_readonly')->isReadonly(),
            "Shipping Address Country field is readonly"
        );
        $this->assertTrue(
            $checkoutForm->Fields()->dataFieldByName($ns . 'BillingAddress_Country_readonly')->isReadonly(),
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
            $ns . 'CustomerDetails_FirstName'    => "",
            $ns . 'CustomerDetails_Surname'      => "",
            $ns . 'CustomerDetails_Email'        => "invalid-email",
            $ns . 'ShippingAddress_Country'      => "NZ",
            $ns . 'ShippingAddress_Address'      => "",
            $ns . 'ShippingAddress_AddressLine2' => "",
            $ns . 'ShippingAddress_City'         => "",
            $ns . 'ShippingAddress_State'        => "",
            $ns . 'ShippingAddress_PostalCode'   => "",
            $ns . 'ShippingAddress_Phone'        => "",
            $ns . 'BillingAddress_Country'       => "NZ",
            $ns . 'BillingAddress_Address'       => "",
            $ns . 'BillingAddress_AddressLine2'  => "",
            $ns . 'BillingAddress_City'          => "",
            $ns . 'BillingAddress_State'         => "",
            $ns . 'BillingAddress_PostalCode'    => "",
            $ns . 'BillingAddress_Phone'         => "",
            $ns . 'Payment_PaymentMethod'        => "",
            $ns . 'Notes_Notes'                  => "",
            $ns . 'Terms_ReadTermsAndConditions' => "",
        ];
        $checkoutForm->loadDataFrom($invalidData, 1);
        $valid = $checkoutForm->validate()->isValid();
        $errors = $checkoutForm->getValidator()->getErrors();
        $this->assertFalse($valid, 'Form should be invalid with empty and incorrect data');
        $this->assertNotEmpty($errors, 'There should be validation errors');
    }

    /**
     * Regression test: the components' required fields must be enforced by the form's validator.
     * The terms checkbox is only required by Terms::getRequiredFields() (its validateData() accepts anything).
     */
    public function testTermsMustBeAcceptedWhenATermsPageIsSet(): void
    {
        $this->setTermsPage();
        $ns = 'SilverShop-Checkout-Component-';

        $data = $this->validData($ns);
        $data[$ns . 'Terms_ReadTermsAndConditions'] = '';
        $result = $this->orderForm()->loadDataFrom($data, 1)->validate();
        $this->assertFalse($result->isValid(), 'An order must not be placed without accepting the terms');
        $this->assertContains($ns . 'Terms_ReadTermsAndConditions', $this->errorFields($result->getMessages()));

        $data[$ns . 'Terms_ReadTermsAndConditions'] = '1';
        $result = $this->orderForm()->loadDataFrom($data, 1)->validate();
        $this->assertTrue($result->isValid(), print_r($result->getMessages(), true));
    }

    public function testRequiredAddressFieldsAreEnforced(): void
    {
        $ns = 'SilverShop-Checkout-Component-';
        $data = $this->validData($ns);
        $data[$ns . 'ShippingAddress_City'] = '';

        $result = $this->orderForm()->loadDataFrom($data, 1)->validate();

        $this->assertFalse($result->isValid(), 'A required address field must not be empty');
        $this->assertContains($ns . 'ShippingAddress_City', $this->errorFields($result->getMessages()));
    }

    public function testRequiredFieldsAreMarkedAsRequired(): void
    {
        $this->setTermsPage();
        $ns = 'SilverShop-Checkout-Component-';
        $fields = $this->orderForm()->Fields();

        $this->assertTrue($fields->dataFieldByName($ns . 'CustomerDetails_Email')->Required());
        $this->assertTrue($fields->dataFieldByName($ns . 'ShippingAddress_City')->Required());
        $this->assertTrue($fields->dataFieldByName($ns . 'Terms_ReadTermsAndConditions')->Required());
    }

    /**
     * Regression test: every error a checkout component reports must reach the customer, not only the first one.
     */
    public function testEveryComponentErrorIsReported(): void
    {
        $form = $this->orderFormWithComponentErrors([
            ['message' => 'Your card has expired'],
            ['message' => 'Your card was declined'],
        ]);
        $result = $form->loadDataFrom($this->validData('SilverShop-Checkout-Component-'), 1)->validate();

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Your card has expired', $form->getMessage(), 'Every component error must be shown');
        $this->assertStringContainsString('Your card was declined', $form->getMessage(), 'Every component error must be shown');
    }

    /**
     * Regression test: an error a checkout component reports for one of its fields must be shown on that field.
     */
    public function testComponentErrorsAreReportedOnTheirField(): void
    {
        $form = $this->orderFormWithComponentErrors([
            ['field' => 'DeliveryDate', 'message' => 'We do not deliver on Sundays'],
            ['field' => 'VatNumber', 'message' => 'This is not a valid VAT number'],
        ]);
        $result = $form->loadDataFrom($this->validData('SilverShop-Checkout-Component-'), 1)->validate();
        $ns = ErrorReportingComponent::create()->name() . '_';

        $this->assertFalse($result->isValid());
        $this->assertSame(
            'We do not deliver on Sundays',
            $form->Fields()->dataFieldByName($ns . 'DeliveryDate')->getMessage(),
            'A component error must be shown on its field'
        );
        $this->assertSame(
            'This is not a valid VAT number',
            $form->Fields()->dataFieldByName($ns . 'VatNumber')->getMessage(),
            'A component error must be shown on its field'
        );
    }

    /**
     * Regression test: an empty customer field is flagged by the required check and by CustomerDetails::validateData();
     * the customer must see that once, on the field.
     */
    public function testAnEmptyCustomerFieldIsReportedOnce(): void
    {
        $ns = 'SilverShop-Checkout-Component-';
        $data = $this->validData($ns);
        $data[$ns . 'CustomerDetails_FirstName'] = '';

        $result = $this->orderForm()->loadDataFrom($data, 1)->validate();

        $this->assertFalse($result->isValid());
        $this->assertSame(
            [$ns . 'CustomerDetails_FirstName'],
            array_column($result->getMessages(), 'fieldName'),
            'An empty first name must be reported once, on its field. Reported: ' . $this->describe($result->getMessages())
        );
    }

    /**
     * @param array<int, array{message: string, field?: string, code?: string}> $errors
     */
    private function orderFormWithComponentErrors(array $errors): CheckoutForm
    {
        $config = SinglePageCheckoutComponentConfig::create(ShoppingCart::curr());
        $config->addComponent(ErrorReportingComponent::create($errors));

        return CheckoutForm::create($this->checkoutcontroller, 'OrderForm', $config);
    }

    /**
     * @param array<int|string, array<string, mixed>> $messages
     */
    private function describe(array $messages): string
    {
        return implode('; ', array_map(
            static fn (array $message): string => sprintf('[%s] %s', $message['fieldName'] ?: 'form', $message['message']),
            $messages
        ));
    }

    private function orderForm(): CheckoutForm
    {
        return CheckoutForm::create(
            $this->checkoutcontroller,
            'OrderForm',
            SinglePageCheckoutComponentConfig::create(ShoppingCart::curr())
        );
    }

    private function setTermsPage(): void
    {
        $terms = SiteTree::create(['Title' => 'Terms and Conditions', 'URLSegment' => 'terms']);
        $terms->write();
        $terms->publishSingle();
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->TermsPageID = $terms->ID;
        $siteConfig->write();
    }

    /**
     * @param array<int|string, array<string, mixed>> $messages
     * @return string[]
     */
    private function errorFields(array $messages): array
    {
        return array_values(array_filter(array_map(
            static fn (array $message): string => (string) ($message['fieldName'] ?? ''),
            $messages
        )));
    }

    /**
     * @return array<string, string>
     */
    private function validData(string $ns): array
    {
        return [
            $ns . 'CustomerDetails_FirstName'    => 'Jane',
            $ns . 'CustomerDetails_Surname'      => 'Smith',
            $ns . 'CustomerDetails_Email'        => 'janesmith@example.com',
            $ns . 'ShippingAddress_Country'      => 'NZ',
            $ns . 'ShippingAddress_Address'      => '1234 Green Lane',
            $ns . 'ShippingAddress_AddressLine2' => 'Building 2',
            $ns . 'ShippingAddress_City'         => 'Bleasdfweorville',
            $ns . 'ShippingAddress_State'        => 'Trumpo',
            $ns . 'ShippingAddress_PostalCode'   => '4123',
            $ns . 'ShippingAddress_Phone'        => '032092277',
            $ns . 'BillingAddress_Country'       => 'NZ',
            $ns . 'BillingAddress_Address'       => '1234 Green Lane',
            $ns . 'BillingAddress_AddressLine2'  => 'Building 2',
            $ns . 'BillingAddress_City'          => 'Bleasdfweorville',
            $ns . 'BillingAddress_State'         => 'Trumpo',
            $ns . 'BillingAddress_PostalCode'    => '4123',
            $ns . 'BillingAddress_Phone'         => '032092277',
            $ns . 'Payment_PaymentMethod'        => 'Dummy',
            $ns . 'Notes_Notes'                  => 'Leave it around the back',
            $ns . 'Terms_ReadTermsAndConditions' => '1',
        ];
    }

    public function testCheckoutFormUsesAddressBookDefaultsForLoggedInMember(): void
    {
        $member = $this->objFromFixture(Member::class, 'joebloggs');
        $this->logInAs($member);
        $order = ShoppingCart::curr();

        $legacyAddress = Address::create();
        $legacyAddress->update([
            'Country' => 'NZ',
            'Address' => '99 Legacy Road',
            'City' => 'Oldtown',
            'State' => 'Legacy',
        ]);
        $legacyAddress->write();

        $savedAddress = Address::create();
        $savedAddress->update([
            'Country' => 'US',
            'Address' => '12 Account Street',
            'City' => 'Profile City',
            'State' => 'Profile State',
            'MemberID' => $member->ID,
        ]);
        $savedAddress->write();

        $member->DefaultShippingAddressID = $savedAddress->ID;
        $member->DefaultBillingAddressID = $savedAddress->ID;
        $member->write();

        $order->MemberID = $member->ID;
        $order->ShippingAddressID = $legacyAddress->ID;
        $order->BillingAddressID = $legacyAddress->ID;
        $order->write();

        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create($order);
        $checkoutForm = CheckoutForm::create($this->checkoutcontroller, 'OrderForm', $singlePageCheckoutComponentConfig);
        $ns = 'SilverShop-Checkout-Component-';

        $shippingAddressID = $checkoutForm->Fields()->dataFieldByName($ns . 'AddressBookShipping_ShippingAddressID');
        $billingAddressID = $checkoutForm->Fields()->dataFieldByName($ns . 'AddressBookBilling_BillingAddressID');

        $this->assertNotNull($shippingAddressID);
        $this->assertNotNull($billingAddressID);
        $this->assertSame((string)$savedAddress->ID, (string)$shippingAddressID->getValue());
        $this->assertSame((string)$savedAddress->ID, (string)$billingAddressID->getValue());
        $this->assertSame(
            $savedAddress->Address,
            (string)$checkoutForm->Fields()->dataFieldByName($ns . 'AddressBookShipping_Address')->getValue()
        );
        $this->assertSame(
            $savedAddress->Address,
            (string)$checkoutForm->Fields()->dataFieldByName($ns . 'AddressBookBilling_Address')->getValue()
        );
    }
}

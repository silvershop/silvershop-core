<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout\Component;

use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\CheckoutConfig;
use SilverShop\Checkout\Component\AddressBookShipping;
use SilverShop\Checkout\Component\CheckoutComponent;
use SilverShop\Checkout\Component\Membership;
use SilverShop\Checkout\Component\OnsitePayment;
use SilverShop\Checkout\Component\Payment;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Omnipay\Model\Payment as OmnipayPayment;
use SilverStripe\Security\Member;

/**
 * The Payment, AddressBook, Membership and OnsitePayment components report each error on the field it is about, with
 * the error type, so the checkout shows it next to that field (see silvershop/silvershop-core#937).
 *
 * They used to call `addError($message, $fieldName)`. In CMS 6 the second argument of `addError()` is the message
 * type, so the field name became the type (`<span class="message PaymentMethod">`) and the error had no field: it
 * ended up at the top of the form, unstyled as an error.
 */
class ComponentFieldErrorsTest extends SapphireTest
{
    protected $usesDatabase = true;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = Order::create();
        $this->order->write();
        Config::modify()
            ->set(OmnipayPayment::class, 'allowed_gateways', ['Manual'])
            ->set(CheckoutConfig::class, 'member_creation_enabled', true);
    }

    public function testPaymentReportsAMissingMethodOnThePaymentMethodField(): void
    {
        $this->assertSame(
            [['PaymentMethod', ValidationResult::TYPE_ERROR]],
            $this->errors(Payment::create(), [])
        );
    }

    public function testPaymentReportsAnUnsupportedGatewayOnThePaymentMethodField(): void
    {
        $this->assertSame(
            [['PaymentMethod', ValidationResult::TYPE_ERROR]],
            $this->errors(Payment::create(), ['PaymentMethod' => 'NotAGateway'])
        );
    }

    public function testAddressBookReportsAnUnknownSavedAddressOnItsField(): void
    {
        $this->logInWithPermission();

        $this->assertSame(
            [['ShippingAddressID', ValidationResult::TYPE_ERROR]],
            $this->errors(AddressBookShipping::create(), ['ShippingAddressID' => 999999])
        );
    }

    public function testAddressBookReportsAMissingAddressFieldOnThatField(): void
    {
        $this->logInWithPermission();
        // With no saved address chosen the component checks the address's own required fields.
        $required = Address::config()->get('required_fields');

        $errors = $this->errors(AddressBookShipping::create(), ['ShippingAddressID' => 0]);

        $this->assertCount(1, $errors);
        $this->assertContains($errors[0][0], $required, 'on one of the required address fields');
        $this->assertSame(ValidationResult::TYPE_ERROR, $errors[0][1]);
    }

    public function testMembershipReportsATakenEmailAndAWeakPasswordOnTheirFields(): void
    {
        Member::create(['Email' => 'taken@example.test'])->write();
        $this->logOut();

        $errors = $this->errors(Membership::create(), ['Email' => 'taken@example.test', 'Password' => 'abc']);

        $this->assertContains(['Email', ValidationResult::TYPE_ERROR], $errors);
        $this->assertContains(['Password', ValidationResult::TYPE_ERROR], $errors);
        $this->assertSame([], array_filter($errors, static fn (array $error): bool => $error[0] === ''), 'none on the form');
    }

    public function testOnsitePaymentReportsAnInvalidCardOnTheCardNumberField(): void
    {
        $this->assertSame(
            [['number', ValidationResult::TYPE_ERROR]],
            $this->errors(OnsitePayment::create(), ['number' => '1234'])
        );
    }

    public function testTheCheckoutShowsTheErrorOnTheNamespacedField(): void
    {
        $config = CheckoutComponentConfig::create($this->order, true);
        $config->addComponent(Payment::create());

        try {
            $config->validateData([]);
            $this->fail('A missing payment method must not validate');
        } catch (ValidationException $exception) {
            $messages = $exception->getResult()->getMessages();
            $this->assertSame(
                [Payment::create()->name() . '_PaymentMethod'],
                array_column($messages, 'fieldName')
            );
            $this->assertSame([ValidationResult::TYPE_ERROR], array_column($messages, 'messageType'));
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array{0: string, 1: string}> [fieldName, messageType] per error
     */
    private function errors(CheckoutComponent $component, array $data): array
    {
        try {
            $component->validateData($this->order, $data);
        } catch (ValidationException $exception) {
            return array_values(array_map(
                static fn (array $message): array => [(string) $message['fieldName'], (string) $message['messageType']],
                $exception->getResult()->getMessages()
            ));
        }
        $this->fail('Expected a validation error');
    }
}

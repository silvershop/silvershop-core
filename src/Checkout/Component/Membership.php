<?php

declare(strict_types=1);

namespace SilverShop\Checkout\Component;

use SilverStripe\Security\Validation\PasswordValidator;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Security\Validation\RulesPasswordValidator;
use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\ShopMemberFactory;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Extension\MemberExtension;
use SilverShop\Model\Order;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Provides:
 *    - member identifier, and password fields.
 *    - required membership fields
 *    - validating data
 */
class Membership extends CheckoutComponent
{
    protected $confirmed;

    /**
     * @var RulesPasswordValidator
     */
    protected $passwordValidator;

    protected $dependson = [
        CustomerDetails::class,
    ];

    public function __construct($confirmed = true, $validator = null)
    {
        $this->confirmed = $confirmed;

        if (!$validator) {
            $this->passwordValidator = Member::password_validator();

            if (!$this->passwordValidator instanceof PasswordValidator) {
                $this->passwordValidator = RulesPasswordValidator::create();
                $this->passwordValidator->setMinLength(5);
                $this->passwordValidator->setTestNames(
                    ["lowercase", "uppercase", "digits", "punctuation"]
                );
            }
        }
    }

    public function getFormFields(Order $order): FieldList
    {
        $fieldList = FieldList::create();

        if (Security::getCurrentUser()) {
            return $fieldList;
        }

        $idField = Member::config()->get('unique_identifier_field');

        if (!$order->{$idField}) {
            $fieldList->push(TextField::create($idField, $idField));
        }

        $fieldList->push($this->getPasswordField());

        return $fieldList;
    }

    public function getRequiredFields(Order $order): array
    {
        if (Security::getCurrentUser() || !Checkout::membership_required()) {
            return [];
        }

        return [
            Member::config()->get('unique_identifier_field'),
            'Password',
        ];
    }

    public function getPasswordField(): ConfirmedPasswordField|PasswordField
    {
        if ($this->confirmed) {
            //relies on fix: https://github.com/silverstripe/silverstripe-framework/pull/2757
            return ConfirmedPasswordField::create('Password', _t('SilverShop\Checkout\CheckoutField.Password', 'Password'))
                ->setCanBeEmpty(!Checkout::membership_required());
        }

        return PasswordField::create('Password', _t('SilverShop\Checkout\CheckoutField.Password', 'Password'));
    }

    public function validateData(Order $order, array $data): bool
    {
        if (Security::getCurrentUser()) {
            return true;
        }

        $validationResult = ValidationResult::create();
        if (Checkout::membership_required() || !empty($data['Password'])) {
            $member = Member::create($data);
            $idfield = Member::config()->get('unique_identifier_field');
            $idval = $this->identifier($order, $data);
            // get localized field labels
            $fieldLabels = $member->fieldLabels(false);
            // if a localized value exists, use this for our error-message
            $fieldLabel = isset($fieldLabels[$idfield]) ? $fieldLabels[$idfield] : $idfield;
            if ($idval === '') {
                // Checked here, before the checkout saves anything; ShopMemberFactory would only refuse it later.
                $validationResult->addFieldError(
                    $idfield,
                    _t(
                        __CLASS__ . '.IdentifierRequired',
                        'Enter your {Field} to create an account',
                        ['Field' => $fieldLabel]
                    )
                );
            } elseif (MemberExtension::get_by_identifier($idval) instanceof Member) {
                $validationResult->addFieldError(
                    $idfield,
                    _t(
                        'SilverShop\Checkout\Checkout.MemberExists',
                        'A member already exists with the {Field} {Identifier}',
                        '',
                        ['Field' => $fieldLabel, 'Identifier' => $idval]
                    )
                );
            }

            $passwordResult = $this->passwordValidator->validate((string) ($data['Password'] ?? ''), $member);

            if (!$passwordResult->isValid()) {
                foreach ($passwordResult->getMessages() as $message) {
                    $validationResult->addFieldError('Password', $message['message']);
                }
            }
        }

        if (!$validationResult->isValid()) {
            throw ValidationException::create($validationResult);
        }

        return true;
    }

    /**
     * The identifier (usually Email) for the new account: the submitted one, or the order's when the field isn't shown.
     */
    private function identifier(Order $order, array $data): string
    {
        $idfield = Member::config()->get('unique_identifier_field');
        $submitted = trim((string) ($data[$idfield] ?? ''));

        return $submitted !== '' ? $submitted : trim((string) $order->{$idfield});
    }

    public function getData(Order $order): array
    {
        $data = [];

        if ($member = Security::getCurrentUser()) {
            $idf = Member::config()->get('unique_identifier_field');
            $data[$idf] = $member->{$idf};
        }

        return $data;
    }

    /**
     * @throws ValidationException
     */
    public function setData(Order $order, array $data): Order
    {
        if (Security::getCurrentUser()) {
            return $order;
        }

        if (!Checkout::membership_required() && empty($data['Password'])) {
            return $order;
        }

        // The identifier field is only shown while the order has none (see getFormFields()); otherwise use the order's.
        $data[Member::config()->get('unique_identifier_field')] = $this->identifier($order, $data);

        $shopMemberFactory = new ShopMemberFactory();
        $member = $shopMemberFactory->create($data);
        $member->write();

        $customer_group = ShopConfigExtension::current()->CustomerGroup();
        if ($customer_group->exists()) {
            $member->Groups()->add($customer_group);
        }

        // Log-in the current member
        Injector::inst()->get(IdentityStore::class)->logIn($member);

        if ($order->BillingAddressID) {
            $address = $order->getBillingAddress();
            $address->MemberID = $member->ID;
            $address->write();
            $member->DefaultBillingAddressID = $order->BillingAddressID;
        }

        if ($order->ShippingAddressID) {
            $address = $order->getShippingAddress();
            $address->MemberID = $member->ID;
            $address->write();
            $member->DefaultShippingAddressID = $order->ShippingAddressID;
        }

        if ($member->isChanged()) {
            $member->write();
        }

        return $order;
    }

    public function setConfirmed($confirmed): static
    {
        $this->confirmed = $confirmed;
        return $this;
    }
}

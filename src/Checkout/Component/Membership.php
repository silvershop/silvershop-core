<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\ShopMemberFactory;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Extension\MemberExtension;
use SilverShop\Model\Order;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordValidator;
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

    protected $passwordvalidator;

    protected $dependson = [
        CustomerDetails::class,
    ];

    public function __construct($confirmed = true, $validator = null)
    {
        $this->confirmed = $confirmed;
        if (!$validator) {
            $this->passwordvalidator = Member::password_validator();
            if (!$this->passwordvalidator) {
                $this->passwordvalidator = PasswordValidator::create();
                $this->passwordvalidator->minLength(5);
                $this->passwordvalidator->characterStrength(
                    2,
                    ["lowercase", "uppercase", "digits", "punctuation"]
                );
            }
        }
    }

    public function getFormFields(Order $order, Form $form = null)
    {
        $fields = FieldList::create();
        if (Security::getCurrentUser()) {
            return $fields;
        }
        $idfield = Member::config()->unique_identifier_field;
        if (!$order->{$idfield} && ($form && !$form->Fields()->fieldByName($idfield))) {
            //TODO: scaffold the correct id field type
            $fields->push(TextField::create($idfield, $idfield));
        }
        $fields->push($this->getPasswordField());
        return $fields;
    }

    public function getRequiredFields(Order $order)
    {
        if (Security::getCurrentUser() || !Checkout::membership_required()) {
            return array();
        }
        return array(
            Member::config()->unique_identifier_field,
            'Password',
        );
    }

    public function getPasswordField()
    {
        if ($this->confirmed) {
            //relies on fix: https://github.com/silverstripe/silverstripe-framework/pull/2757
            return ConfirmedPasswordField::create('Password', _t('SilverShop\Checkout\CheckoutField.Password', 'Password'))
                ->setCanBeEmpty(!Checkout::membership_required());
        }
        return PasswordField::create('Password', _t('SilverShop\Checkout\CheckoutField.Password', 'Password'));
    }

    public function validateData(Order $order, array $data)
    {
        if (Security::getCurrentUser()) {
            return;
        }
        $result = ValidationResult::create();
        if (Checkout::membership_required() || !empty($data['Password'])) {
            $member = Member::create($data);
            $idfield = Member::config()->unique_identifier_field;
            $idval = $data[$idfield];
            if (MemberExtension::get_by_identifier($idval)) {
                // get localized field labels
                $fieldLabels = $member->fieldLabels(false);
                // if a localized value exists, use this for our error-message
                $fieldLabel = isset($fieldLabels[$idfield]) ? $fieldLabels[$idfield] : $idfield;
                $result->addError(
                    _t(
                        'SilverShop\Checkout\Checkout.MemberExists',
                        'A member already exists with the {Field} {Identifier}',
                        '',
                        array('Field' => $fieldLabel, 'Identifier' => $idval)
                    ),
                    $idfield
                );
            }
            $passwordresult = $this->passwordvalidator->validate($data['Password'], $member);
            if (!$passwordresult->isValid()) {
                foreach ($passwordresult->getMessages() as $message) {
                    $result->addError($message['message'], "Password");
                }
            }
        }
        if (!$result->isValid()) {
            throw new ValidationException($result);
        }
    }

    public function getData(Order $order)
    {
        $data = array();

        if ($member = Security::getCurrentUser()) {
            $idf = Member::config()->unique_identifier_field;
            $data[$idf] = $member->{$idf};
        }
        return $data;
    }

    /**
     * @throws ValidationException
     */
    public function setData(Order $order, array $data)
    {
        if (Security::getCurrentUser()) {
            return;
        }
        if (!Checkout::membership_required() && empty($data['Password'])) {
            return;
        }

        $factory = new ShopMemberFactory();
        $member = $factory->create($data);
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
    }

    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }
}

<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Security;

class CustomerDetails extends CheckoutComponent
{
    protected $requiredfields = [
        'FirstName',
        'Surname',
        'Email',
    ];

    public function getFormFields(Order $order): FieldList
    {
        return FieldList::create(
            $firstname = TextField::create('FirstName', _t('SilverShop\Model\Order.db_FirstName', 'First Name')),
            $surname = TextField::create('Surname', _t('SilverShop\Model\Order.db_Surname', 'Surname')),
            $email = EmailField::create('Email', _t('SilverShop\Model\Order.db_Email', 'Email'))
        );
    }

    public function validateData(Order $order, array $data): bool
    {
        $result = ValidationResult::create();
        foreach ($this->getRequiredFields($order) as $field_name) {
            if (!isset($field_name)) {
                $result->addError(
                    _t(__CLASS__ . '.No' . $field_name, "{$field_name} is required"),
                    "CustomerDetails"
                );
                throw new ValidationException($result);
            }
        }
        return true;
    }

    public function getData(Order $order): array
    {
        if ($order->FirstName || $order->Surname || $order->Email) {
            return [
                'FirstName' => $order->FirstName,
                'Surname' => $order->Surname,
                'Email' => $order->Email,
            ];
        }
        if ($member = Security::getCurrentUser()) {
            return [
                'FirstName' => $member->FirstName,
                'Surname' => $member->Surname,
                'Email' => $member->Email,
            ];
        }
        return [];
    }

    public function setData(Order $order, array $data): Order
    {
        $order->update($data);
        $order->write();
        return $order;
    }
}

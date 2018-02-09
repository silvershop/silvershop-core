<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Security;

class CustomerDetails extends CheckoutComponent
{
    protected $requiredfields = [
        'FirstName',
        'Surname',
        'Email',
    ];

    public function getFormFields(Order $order)
    {
        $fields = FieldList::create(
            $firstname = TextField::create('FirstName', _t('SilverShop\Model\Order.db_FirstName', 'First Name')),
            $surname = TextField::create('Surname', _t('SilverShop\Model\Order.db_Surname', 'Surname')),
            $email = EmailField::create('Email', _t('SilverShop\Model\Order.db_Email', 'Email'))
        );

        return $fields;
    }

    public function validateData(Order $order, array $data)
    {
        //all fields are required
    }

    public function getData(Order $order)
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
        return array();
    }

    public function setData(Order $order, array $data)
    {
        $order->update($data);
        $order->write();
    }
}

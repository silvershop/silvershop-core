<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;

class Notes extends CheckoutComponent
{
    public function getFormFields(Order $order)
    {
        return FieldList::create(
            TextareaField::create('Notes', _t('SilverShop\Model\Order.db_Notes', 'Message'))
        );
    }

    public function validateData(Order $order, array $data)
    {
    }

    public function setData(Order $order, array $data)
    {
        if (isset($data['Notes'])) {
            $order->Notes = $data['Notes'];
        }
        //TODO: save this to an order log

        $order->write();
    }

    public function getData(Order $order)
    {
        return [
            'Notes' => $order->Notes,
        ];
    }
}

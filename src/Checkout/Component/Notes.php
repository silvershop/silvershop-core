<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;

class Notes extends CheckoutComponent
{
    public function getFormFields(Order $order): FieldList
    {
        return FieldList::create(
            TextareaField::create('Notes', _t('SilverShop\Model\Order.db_Notes', 'Message'))
        );
    }

    public function validateData(Order $order, array $data): bool
    {
        return true;
    }

    public function setData(Order $order, array $data): Order
    {
        if (isset($data['Notes'])) {
            $order->Notes = $data['Notes'];
        }
        //TODO: save this to an order log

        $order->write();
        return $order;
    }

    public function getData(Order $order): array
    {
        return [
            'Notes' => $order->Notes,
        ];
    }
}

<?php

namespace SilverShop\Core\Model;


use SilverStripe\Core\Extension;


/**
 */
class ShopPaymentService extends Extension
{
    public function updatePartialPayment($newPayment, $originalPayment)
    {
        $newPayment->OrderID = $originalPayment->OrderID;
    }
}

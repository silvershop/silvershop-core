<?php

/**
 */
class ShopPaymentService extends Extension
{
    public function updatePartialPayment($newPayment, $originalPayment)
    {
        $newPayment->OrderID = $originalPayment->OrderID;
    }
}

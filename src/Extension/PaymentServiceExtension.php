<?php

namespace SilverShop\Extension;

use SilverStripe\Core\Extension;

/**
 * Extension to the Omnipay PaymentService class
 */
class PaymentServiceExtension extends Extension
{
    public function updatePartialPayment($newPayment, $originalPayment)
    {
        $newPayment->OrderID = $originalPayment->OrderID;
    }
}

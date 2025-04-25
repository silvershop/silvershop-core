<?php

namespace SilverShop\Extension;

use SilverStripe\Core\Extension;

/**
 * Extension to the Omnipay PaymentService class
 */
class PaymentServiceExtension extends Extension
{
    public function updatePartialPayment($newPayment, $originalPayment): void
    {
        $newPayment->OrderID = $originalPayment->OrderID;
    }
}

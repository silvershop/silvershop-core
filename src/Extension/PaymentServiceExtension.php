<?php

namespace SilverShop\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Omnipay\Service\PaymentService;

/**
 * Extension to the Omnipay PaymentService class
 * @extends Extension<(PaymentService & static)>
 */
class PaymentServiceExtension extends Extension
{
    public function updatePartialPayment($newPayment, $originalPayment): void
    {
        $newPayment->OrderID = $originalPayment->OrderID;
    }
}

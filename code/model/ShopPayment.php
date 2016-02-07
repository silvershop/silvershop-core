<?php

/**
 * Customisations to {@link Payment} specifically for the shop module.
 *
 * @package shop
 */
class ShopPayment extends DataExtension
{
    private static $has_one = array(
        'Order' => 'Order',
    );

    public function onCaptured($response)
    {
        $order = $this->owner->Order();
        if ($order->exists()) {
            OrderProcessor::create($order)->completePayment();
        }
    }
}

<?php

use SilverStripe\Omnipay\Service\ServiceResponse;

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

    public function onAwaitingAuthorized(ServiceResponse $response)
    {
        $this->placeOrder();
    }

    public function onAwaitingCaptured(ServiceResponse $response)
    {
        $this->placeOrder();
    }

    public function onAuthorized(ServiceResponse $response)
    {
        $this->placeOrder();
    }

    public function onCaptured(ServiceResponse $response)
    {
        $order = $this->owner->Order();
        if ($order->exists()) {
            OrderProcessor::create($order)->completePayment();
        }
    }

    protected function placeOrder()
    {
        $order = $this->owner->Order();
        if ($order->exists()) {
            OrderProcessor::create($order)->placeOrder();
        }
    }
}

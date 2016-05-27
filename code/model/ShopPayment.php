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
        // ensure order is being reloaded from DB, to prevent dealing with stale data!
        /** @var Order $order */
        $order = Order::get()->byID($this->owner->OrderID);
        if ($order && $order->exists()) {
            OrderProcessor::create($order)->completePayment();
        }
    }

    protected function placeOrder()
    {
        // ensure order is being reloaded from DB, to prevent dealing with stale data!
        /** @var Order $order */
        $order = Order::get()->byID($this->owner->OrderID);
        if ($order && $order->exists()) {
            OrderProcessor::create($order)->placeOrder();
        }
    }
}

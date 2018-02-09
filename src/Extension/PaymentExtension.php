<?php

namespace SilverShop\Extension;

use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverStripe\Omnipay\Service\ServiceResponse;
use SilverStripe\ORM\DataExtension;

/**
 * Customisations to {@link Payment} specifically for the shop module.
 */
class PaymentExtension extends DataExtension
{
    private static $has_one = [
        'Order' => Order::class,
    ];

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
        /**
         * @var Order $order
         */
        $order = Order::get()->byID($this->owner->OrderID);
        if ($order && $order->exists()) {
            OrderProcessor::create($order)->completePayment();
        }
    }

    protected function placeOrder()
    {
        // ensure order is being reloaded from DB, to prevent dealing with stale data!
        /**
         * @var Order $order
         */
        $order = Order::get()->byID($this->owner->OrderID);
        if ($order && $order->exists()) {
            OrderProcessor::create($order)->placeOrder();
        }
    }
}

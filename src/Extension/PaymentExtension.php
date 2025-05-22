<?php

namespace SilverShop\Extension;

use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverStripe\Core\Extension;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceResponse;

/**
 * Customisations to {@link Payment} specifically for the shop module.
 * @property int $OrderID
 * @method Order Order()
 * @extends Extension<(Payment & static)>
 */
class PaymentExtension extends Extension
{
    private static array $has_one = [
        'Order' => Order::class,
    ];

    public function onAwaitingAuthorized(ServiceResponse $response): void
    {
        $this->placeOrder();
    }

    public function onAwaitingCaptured(ServiceResponse $response): void
    {
        $this->placeOrder();
    }

    public function onAuthorized(ServiceResponse $response): void
    {
        $this->placeOrder();
    }

    public function onCaptured(ServiceResponse $response): void
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

    protected function placeOrder(): void
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

<?php

namespace SilverShop\Checkout;

use SilverShop\Checkout\Component\BillingAddress;
use SilverShop\Checkout\Component\CustomerDetails;
use SilverShop\Checkout\Component\Membership;
use SilverShop\Checkout\Component\Notes;
use SilverShop\Checkout\Component\Payment;
use SilverShop\Checkout\Component\ShippingAddress;
use SilverShop\Checkout\Component\Terms;
use SilverShop\Model\Order;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Security\Security;

class SinglePageCheckoutComponentConfig extends CheckoutComponentConfig
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
        $this->addComponent(CustomerDetails::create());
        $this->addComponent(ShippingAddress::create());
        $this->addComponent(BillingAddress::create());
        if (Checkout::member_creation_enabled() && !Security::getCurrentUser()) {
            $this->addComponent(Membership::create());
        }
        if (count(GatewayInfo::getSupportedGateways()) > 1) {
            $this->addComponent(Payment::create());
        }
        $this->addComponent(Notes::create());
        $this->addComponent(Terms::create());
    }
}

<?php

namespace SilverShop\Core\Checkout\Component;

class SinglePageCheckoutComponentConfig extends CheckoutComponentConfig
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
        $this->addComponent(CustomerDetailsCheckoutComponent::create());
        $this->addComponent(ShippingAddressCheckoutComponent::create());
        $this->addComponent(BillingAddressCheckoutComponent::create());
        if (Checkout::member_creation_enabled() && !Member::currentUserID()) {
            $this->addComponent(MembershipCheckoutComponent::create());
        }
        if (count(GatewayInfo::getSupportedGateways()) > 1) {
            $this->addComponent(PaymentCheckoutComponent::create());
        }
        $this->addComponent(NotesCheckoutComponent::create());
        $this->addComponent(TermsCheckoutComponent::create());
    }
}

<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Component\AddressBookBilling;
use SilverShop\Checkout\Component\AddressBookShipping;
use SilverShop\Checkout\CheckoutComponentConfig;

class AddressBook extends Address
{
    private static array $allowed_actions = [
        'shippingaddress',
        'ShippingAddressForm',
        'setshippingaddress',
        'billingaddress',
        'BillingAddressForm',
        'setbillingaddress',
    ];

    public function shippingconfig(): CheckoutComponentConfig
    {
        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr());
        $checkoutComponentConfig->addComponent(AddressBookShipping::create());

        return $checkoutComponentConfig;
    }

    public function billingconfig(): CheckoutComponentConfig
    {
        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr());
        $checkoutComponentConfig->addComponent(AddressBookBilling::create());

        return $checkoutComponentConfig;
    }
}

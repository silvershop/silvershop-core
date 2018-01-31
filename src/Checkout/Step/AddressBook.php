<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Component\AddressBookBilling;
use SilverShop\Checkout\Component\AddressBookShipping;
use SilverShop\Checkout\CheckoutComponentConfig;

class AddressBook extends Address
{
    private static $allowed_actions = array(
        'shippingaddress',
        'ShippingAddressForm',
        'setshippingaddress',
        'billingaddress',
        'BillingAddressForm',
        'setbillingaddress',
    );

    public function shippingconfig()
    {
        $config = CheckoutComponentConfig::create(ShoppingCart::curr());
        $config->addComponent(AddressBookShipping::create());

        return $config;
    }

    public function billingconfig()
    {
        $config = CheckoutComponentConfig::create(ShoppingCart::curr());
        $config->addComponent(AddressBookBilling::create());

        return $config;
    }
}

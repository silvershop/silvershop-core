<?php

namespace SilverShop\Core\Checkout\Step;

use SilverShop\Core\Cart\ShoppingCart;
use SilverShop\Core\Checkout\Component\AddressBookBilling;
use SilverShop\Core\Checkout\Component\AddressBookShipping;
use SilverShop\Core\Checkout\Component\CheckoutComponentConfig;

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

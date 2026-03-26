<?php

declare(strict_types=1);

namespace SilverShop\Checkout\Component;

class ShippingAddress extends Address
{
    protected string $addresstype = 'Shipping';
}

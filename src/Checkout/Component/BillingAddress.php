<?php

declare(strict_types=1);

namespace SilverShop\Checkout\Component;

class BillingAddress extends Address
{
    protected string $addresstype = 'Billing';
}

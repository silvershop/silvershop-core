<?php

namespace SilverShop\Core\Checkout;


use SilverStripe\Core\Config\Configurable;

class CheckoutConfig
{
    use Configurable;

    /**
     * @config whether or not members can be created
     * @var bool
     */
    private static $member_creation_enabled = true;

    /**
     * @config whether or not membership is required for checkout (eg. no guest checkout)
     * @var bool
     */
    private static $membership_required     = false;
}

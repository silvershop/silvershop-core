<?php

namespace SilverShop\Checkout;

use SilverStripe\Core\Config\Configurable;

class CheckoutConfig
{
    use Configurable;

    /**
     * Whether or not members can be created
     */
    private static bool $member_creation_enabled = true;

    /**
     * Whether or not membership is required for checkout (eg. no guest checkout)
     */
    private static bool $membership_required     = false;
}

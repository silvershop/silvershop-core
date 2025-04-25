<?php

namespace SilverShop\Checkout;

use SilverStripe\Core\Config\Configurable;

class CheckoutConfig
{
    use Configurable;

    /**
     * Whether or not members can be created
     *
     * @config
     */
    private static bool $member_creation_enabled = true;

    /**
     * Whether or not membership is required for checkout (eg. no guest checkout)
     *
     * @config
     */
    private static bool $membership_required     = false;
}

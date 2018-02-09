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
     * @var    bool
     */
    private static $member_creation_enabled = true;

    /**
     * Whether or not membership is required for checkout (eg. no guest checkout)
     *
     * @config
     * @var    bool
     */
    private static $membership_required     = false;
}

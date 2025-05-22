<?php

namespace SilverShop\Tests\Checkout;

// Extension to Order that will allow us a failed placement
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
class OrderProcessorTest_PlaceFailExtension extends Extension implements TestOnly
{
    private bool $willFail = false;

    public function onPlaceOrder(): void
    {
        // flag this order to fail
        $this->willFail = true;
    }

    public function onAfterWrite(): void
    {
        // fail after writing, so that we can test if DB rollback works as intended
        if ($this->willFail) {
            user_error('Order failed');
        }
    }
}

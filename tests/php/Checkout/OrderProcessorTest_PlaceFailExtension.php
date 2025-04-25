<?php

namespace SilverShop\Tests\Checkout;

// Extension to Order that will allow us a failed placement
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataExtension;

class OrderProcessorTest_PlaceFailExtension extends DataExtension implements TestOnly
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

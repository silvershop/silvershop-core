<?php

declare(strict_types=1);

namespace SilverShop\Tests\Checkout;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Records the order reference that onPlaceOrder handlers (e.g. order emails) see.
 *
 * @extends Extension<\SilverShop\Model\Order>
 */
class OrderProcessorTest_ReferenceRecorder extends Extension implements TestOnly
{
    public static ?string $seen = null;

    public function onPlaceOrder(): void
    {
        self::$seen = (string) $this->getOwner()->getField('Reference');
    }
}

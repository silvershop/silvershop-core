<?php

namespace SilverShop\Tests\Model;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
class OrderTest_TestStatusChangeExtension extends Extension implements TestOnly
{
    public static $stack = [];

    public static function reset(): void
    {
        self::$stack = [];
    }

    public function onStatusChange($fromStatus, $toStatus): void
    {
        self::$stack[] = [
            $fromStatus => $toStatus
        ];
    }
}

<?php

namespace SilverShop\Tests\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataExtension;

class OrderTest_TestStatusChangeExtension extends DataExtension implements TestOnly
{
    public static $stack = [];

    public static function reset()
    {
        self::$stack = [];
    }

    public function onStatusChange($fromStatus, $toStatus)
    {
        self::$stack[] = [
            $fromStatus => $toStatus
        ];
    }
}

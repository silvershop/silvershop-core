<?php

namespace SilverShop\Tests\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataExtension;

class OrderTest_TestStatusChangeExtension extends DataExtension implements TestOnly
{
    public static $stack = array();

    public static function reset()
    {
        self::$stack = array();
    }

    public function onStatusChange($fromStatus, $toStatus)
    {
        self::$stack[] = array(
            $fromStatus => $toStatus
        );
    }
}

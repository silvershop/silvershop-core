<?php

namespace SilverShop\Tests\Tasks;

use SilverShop\Tasks\CartCleanupTask;
use SilverStripe\Dev\TestOnly;

class FakeCartCleanupTask extends CartCleanupTask implements TestOnly
{
    public $log = array();

    public function log($msg)
    {
        $this->log[] = $msg;
    }
}

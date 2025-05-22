<?php

namespace SilverShop\Tests\Tasks;

use SilverShop\Tasks\CartCleanupTask;
use SilverStripe\Dev\TestOnly;

class FakeCartCleanupTask extends CartCleanupTask implements TestOnly
{
    public $log = [];

    public function log($msg): void
    {
        $this->log[] = $msg;
    }
}

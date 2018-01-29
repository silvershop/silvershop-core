<?php

namespace SilverShop\Core\Tests\Tasks;


use SilverShop\Core\Tasks\CartCleanupTask;
use SilverStripe\Dev\TestOnly;

class FakeCartCleanupTask extends CartCleanupTask implements TestOnly
{
    public $log = array();

    public function log($msg)
    {
        $this->log[] = $msg;
    }
}

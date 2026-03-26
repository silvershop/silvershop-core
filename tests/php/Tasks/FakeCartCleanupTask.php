<?php

declare(strict_types=1);

namespace SilverShop\Tests\Tasks;

use SilverShop\Tasks\CartCleanupTask;
use SilverStripe\Dev\TestOnly;

class FakeCartCleanupTask extends CartCleanupTask implements TestOnly
{
    public $log = [];

    protected function log($msg): void
    {
        $this->log[] = $msg;
    }
}

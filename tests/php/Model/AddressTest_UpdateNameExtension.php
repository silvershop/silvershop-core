<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
class AddressTest_UpdateNameExtension extends Extension implements TestOnly
{
    public function updateName(string &$name): void
    {
        $name = $name . ' (extended)';
    }
}

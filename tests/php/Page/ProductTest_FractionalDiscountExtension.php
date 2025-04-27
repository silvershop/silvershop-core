<?php

namespace SilverShop\Tests\Page;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
class ProductTest_FractionalDiscountExtension extends Extension implements TestOnly
{
    public function updateSellingPrice(&$price): void
    {
        $price -= 0.015;
    }
}

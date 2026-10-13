<?php

declare(strict_types=1);

namespace SilverShop\Tests\Fluent;

use SilverShop\Page\Product;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class OrderItemProductExtension extends Extension implements TestOnly
{
    public function updateProduct(&$product, bool $forcecurrent): void
    {
        $product = Product::create(['Title' => 'Replacement']);
    }
}

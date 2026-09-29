<?php

declare(strict_types=1);

namespace SilverShop\Tests\Page;

use SilverShop\Page\Product;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Supplies every product through the overrideProductsShowable hook, so a test can prove the hook
 * short-circuits ProductCategory::ProductsShowable().
 *
 * @extends Extension<static>
 */
class ProductCategoryTest_OverrideExtension extends Extension implements TestOnly
{
    public function overrideProductsShowable(&$override, $recursive): void
    {
        $override = Product::get();
    }
}

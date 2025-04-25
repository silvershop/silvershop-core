<?php

namespace SilverShop\Tasks;

use SilverShop\Page\Product;
use SilverStripe\Control\CliController;

/**
 *
 * @subpackage tasks
 */
class ProductVariationsFromAttributeCombinations extends CliController
{
    public function process(): void
    {
        $products = Product::get();
        if (!$products->count()) {
            return;
        }

        foreach ($products as $product) {
            $product->generateVariationsFromAttributes();
        }
    }
}

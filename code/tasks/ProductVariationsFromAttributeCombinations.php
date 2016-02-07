<?php

/**
 *
 * @subpackage tasks
 */
class ProductVariationsFromAttributeCombinations extends CliController
{
    public function process()
    {

        $products = DataObject::get('Product');
        if (!$products) {
            return;
        }

        foreach ($products as $product) {
            $product->generateVariationsFromAttributes();
        }
    }
}

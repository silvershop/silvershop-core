<?php

namespace SilverShop\Model\Product;

use SilverShop\Page\Product;
use SilverStripe\Versioned\Versioned;

/**
 * Product - Order Item
 * Connects a product to an orde.
 *
 * @property int $ProductVersion
 * @property int $ProductID
 */
class OrderItem extends \SilverShop\Model\OrderItem
{
    private static $db = [
        'ProductVersion' => 'Int',
    ];

    private static $has_one = [
        'Product' => Product::class,
    ];

    private static $table_name = 'SilverShop_Product_OrderItem';

    /**
     * the has_one join field to identify the buyable
     */
    private static $buyable_relationship = 'Product';

    /**
     * Get related product
     *  - live version if in cart, or
     *  - historical version if order is placed
     *
     * @param boolean $forcecurrent - force getting latest version of the product.
     *
     * @return Product
     */
    public function Product($forcecurrent = false)
    {
        //TODO: this might need some unit testing to make sure it compliles with comment description
        //ie use live if in cart (however I see no logic for checking cart status)
        if ($this->ProductID && $this->ProductVersion && !$forcecurrent) {
            return Versioned::get_version(Product::class, $this->ProductID, $this->ProductVersion);
        } elseif ($this->ProductID
            && $product = Versioned::get_one_by_stage(
                Product::class,
                'Live',
                '"SilverShop_Product"."ID"  = ' . $this->ProductID
            )
        ) {
            return $product;
        }
        return null;
    }

    public function onPlacement()
    {
        parent::onPlacement();
        if ($product = $this->Product(true)) {
            $this->ProductVersion = $product->Version;
        }
    }

    public function getTableTitle()
    {
        $product = $this->Product();
        $tabletitle = ($product) ? $product->Title : $this->i18n_singular_name();
        $this->extend('updateTableTitle', $tabletitle);

        return $tabletitle;
    }

    public function Link()
    {
        if ($product = $this->Product()) {
            return $product->Link();
        }
    }
}

<?php

namespace SilverShop\Core\Product;


use SilverStripe\Versioned\Versioned;



class ProductOrderItem extends OrderItem
{
    private static $db      = array(
        'ProductVersion' => 'Int',
    );

    private static $has_one = array(
        'Product' => 'Product',
    );

    /**
     * the has_one join field to identify the buyable
     */
    private static $buyable_relationship = "Product";

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
            return Versioned::get_version('Product', $this->ProductID, $this->ProductVersion);
        } elseif (
            $this->ProductID
            && $product = Versioned::get_one_by_stage(
                "Product",
                "Live",
                "\"Product\".\"ID\"  = " . $this->ProductID
            )
        ) {
            return $product;
        }
        return false;
    }

    public function onPlacement()
    {
        parent::onPlacement();
        if ($product = $this->Product(true)) {
            $this->ProductVersion = $product->Version;
        }
    }

    public function TableTitle()
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

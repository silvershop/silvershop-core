<?php

namespace SilverShop\Core\Product\Variation;

use SilverShop\Core\Product\ProductOrderItem;
use SilverStripe\Versioned\Versioned;

/**
 * Product Variation - Order Item
 * Connects a variation to an order, as a line in the order specifying the particular variation.
 *
 * @package    shop
 * @subpackage variations
 */
class VariationOrderItem extends ProductOrderItem
{
    private static $db = [
        'ProductVariationVersion' => 'Int',
    ];

    private static $has_one = [
        'ProductVariation' => Variation::class
    ];

    private static $buyable_relationship = Variation::class;

    private static $table_name = 'SilverShop_VariationOrderItem';

    /**
     * Overloaded relationship, for getting versioned variations
     *
     * @param boolean $current
     * @return Variation
     */
    public function ProductVariation($forcecurrent = false)
    {
        if ($this->ProductVariationID && $this->ProductVariationVersion && !$forcecurrent) {
            return Versioned::get_version(
                'ProductVariation',
                $this->ProductVariationID,
                $this->ProductVariationVersion
            );
        } elseif ($this->ProductVariationID
            && $product = Variation::get()->byID($this->ProductVariationID)
        ) {
            return $product;
        }
        return null;
    }

    public function SubTitle()
    {
        if ($this->ProductVariation()) {
            return $this->ProductVariation()->getTitle();
        }
        return false;
    }

    public function Image()
    {
        if (($this->ProductVariation()) && $this->ProductVariation()->Image()->exists()) {
            return $this->ProductVariation()->Image();
        }
        return $this->Product()->Image();
    }

    public function Width()
    {
        if ($this->ProductVariation()->Width) {
            return $this->ProductVariation()->Width;
        }
        return $this->Product()->Width;
    }

    public function Height()
    {
        if ($this->ProductVariation()->Height) {
            return $this->ProductVariation()->Height;
        }
        return $this->Product()->Height;
    }

    public function Depth()
    {
        if ($this->ProductVariation()->Depth) {
            return $this->ProductVariation()->Depth;
        }
        return $this->Product()->Depth;
    }

    public function Weight()
    {
        if ($this->ProductVariation()->Weight) {
            return $this->ProductVariation()->Weight;
        }
        return $this->Product()->Weight;
    }
}

<?php

namespace SilverShop\Model\Variation;

use SilverStripe\Versioned\Versioned;

/**
 * Product Variation - Order Item
 * Connects a variation to an order, as a line in the order specifying the particular variation.
 *
 * @property int $ProductVariationVersion
 * @property int $ProductVariationID
 */
class OrderItem extends \SilverShop\Model\Product\OrderItem
{
    private static $db = [
        'ProductVariationVersion' => 'Int',
    ];

    private static $has_one = [
        'ProductVariation' => Variation::class
    ];

    private static $buyable_relationship = 'ProductVariation';

    private static $table_name = 'SilverShop_Variation_OrderItem';

    /**
     * Overloaded relationship, for getting versioned variations
     *
     * @param  boolean $current
     * @return Variation
     */
    public function ProductVariation($forcecurrent = false)
    {
        if ($this->ProductVariationID && $this->ProductVariationVersion && !$forcecurrent) {
            return Versioned::get_version(
                Variation::class,
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
    
    public function onPlacement()
    {
        parent::onPlacement();
        if ($productVariation = $this->ProductVariation(true)) {
            $this->ProductVariationVersion = $productVariation->Version;
        }
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
        if (($variation = $this->ProductVariation()) && $variation->Image()->exists()) {
            return $variation->Image();
        }
        return $this->Product()->Image();
    }

    public function Width()
    {
        if (($variation = $this->ProductVariation()) && $variation->Width) {
            return $variation->Width;
        }
        return $this->Product()->Width;
    }

    public function Height()
    {
        if (($variation = $this->ProductVariation()) && $variation->Height) {
            return $variation->Height;
        }
        return $this->Product()->Height;
    }

    public function Depth()
    {
        if (($variation = $this->ProductVariation()) && $variation->Depth) {
            return $variation->Depth;
        }
        return $this->Product()->Depth;
    }

    public function Weight()
    {
        if (($variation = $this->ProductVariation()) && $variation->Weight) {
            return $variation->Weight;
        }
        return $this->Product()->Weight;
    }
}

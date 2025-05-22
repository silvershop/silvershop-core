<?php

namespace SilverShop\Model\Variation;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\Versioned\Versioned;

/**
 * Product Variation - Order Item
 * Connects a variation to an order, as a line in the order specifying the particular variation.
 *
 * @property int $ProductVariationVersion
 * @property int $ProductVariationID
 * @method Variation ProductVariation()
 */
class OrderItem extends \SilverShop\Model\Product\OrderItem
{
    private static array $db = [
        'ProductVariationVersion' => 'Int',
    ];

    private static array $has_one = [
        'ProductVariation' => Variation::class
    ];

    private static string $buyable_relationship = 'ProductVariation';

    private static string $table_name = 'SilverShop_Variation_OrderItem';

    /**
     * Overloaded relationship, for getting versioned variations
     *
     * @param  boolean $forcecurrent
     */
    public function ProductVariation($forcecurrent = false): DataObject|Versioned|null
    {
        if ($this->ProductVariationID && $this->ProductVariationVersion && !$forcecurrent) {
            return Versioned::get_version(
                Variation::class,
                $this->ProductVariationID,
                $this->ProductVariationVersion
            );
        }
        if ($this->ProductVariationID
            && $product = Variation::get()->byID($this->ProductVariationID)) {
            return $product;
        }
        return null;
    }

    public function onPlacement(): void
    {
        parent::onPlacement();
        if ($productVariation = $this->ProductVariation(true)) {
            $this->ProductVariationVersion = $productVariation->Version;
        }
    }

    public function SubTitle(): false|string
    {
        if ($this->ProductVariation()) {
            return $this->ProductVariation()->getTitle();
        }
        return false;
    }

    public function Image(): Image
    {
        if (($variation = $this->ProductVariation()) && $variation->Image()->exists()) {
            return $variation->Image();
        }
        return $this->Product()->Image();
    }

    public function Width(): float
    {
        if (($variation = $this->ProductVariation()) && $variation->Width) {
            return $variation->Width;
        }
        return $this->Product()->Width;
    }

    public function Height(): float
    {
        if (($variation = $this->ProductVariation()) && $variation->Height) {
            return $variation->Height;
        }
        return $this->Product()->Height;
    }

    public function Depth(): float
    {
        if (($variation = $this->ProductVariation()) && $variation->Depth) {
            return $variation->Depth;
        }
        return $this->Product()->Depth;
    }

    public function Weight(): float
    {
        if (($variation = $this->ProductVariation()) && $variation->Weight) {
            return $variation->Weight;
        }
        return $this->Product()->Weight;
    }
}

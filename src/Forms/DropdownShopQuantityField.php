<?php

namespace SilverShop\Forms;

use SilverStripe\Forms\DropdownField;

/**
 * A links-based field for increasing, decreasing and setting a order item quantity
 *
 * @subpackage forms
 */
class DropdownShopQuantityField extends ShopQuantityField
{
    protected $template = self::class;

    /**
     * The max amount to enter
     *
     * @config
     * @var    int
     */
    private static $max = 100;

    public function Field()
    {
        $qtyArray = array();
        for ($r = 1; $r <= $this->config()->max; $r++) {
            $qtyArray[$r] = $r;
        }

        return DropdownField::create(
            $this->MainID() . '_Quantity',
            // this title currently doesn't show up in the front end, better assign a translation anyway.
            _t('SilverShop\Model\Order.Quantity', "Quantity"),
            $qtyArray,
            ($this->item->Quantity) ? $this->item->Quantity : ""
        );
    }
}

<?php

namespace SilverShop\Core\Cart;

use SilverStripe\Forms\DropdownField;

/**
 * A links-based field for increasing, decreasing and setting a order item quantity
 *
 * @subpackage forms
 */
class DropdownShopQuantityField extends ShopQuantityField
{
    protected $template = 'DropdownShopQuantityField';

    protected $max      = 100;

    public function Field()
    {
        $qtyArray = array();
        for ($r = 1; $r <= $this->max; $r++) {
            $qtyArray[$r] = $r;
        }
        return DropdownField::create(
            $this->MainID() . '_Quantity',
            // this title currently doesn't show up in the front end, better assign a translation anyway.
            _t('Order.Quantity', "Quantity"),
            $qtyArray,
            ($this->item->Quantity) ? $this->item->Quantity : ""
        );
    }
}

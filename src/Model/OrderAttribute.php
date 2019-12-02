<?php

namespace SilverShop\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBCurrency;

/**
 * A single line in an order. This could be an item, or a subtotal line.
 *
 * @see OrderItem
 * @see OrderModifier
 *
 * @property DBCurrency $CalculatedTotal
 * @property int $OrderID
 * @method   Order Order()
 */
class OrderAttribute extends DataObject
{
    private static $singular_name = 'Attribute';

    private static $plural_name = 'Attributes';

    private static $db = [
        'CalculatedTotal' => 'Currency',
    ];

    private static $has_one = [
        'Order' => Order::class,
    ];

    private static $casting = [
        'TableTitle' => 'Text',
        'CartTitle' => 'Text',
    ];

    private static $table_name = 'SilverShop_OrderAttribute';

    public function canCreate($member = null, $context = array())
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function isLive()
    {
        return (!$this->isInDB() || $this->Order()->IsCart());
    }

    /**
     * Produces a title for use in templates.
     *
     * @return string
     */
    public function getTableTitle()
    {
        $title = $this->i18n_singular_name();
        $this->extend('updateTableTitle', $title);
        return $title;
    }

    public function getCartTitle()
    {
        $title = $this->getTableTitle();
        $this->extend('updateCartTitle', $title);
        return $title;
    }

    public function ShowInTable()
    {
        $showInTable = true;
        $this->extend('updateShowInTable', $showInTable);
        return $showInTable;
    }
}

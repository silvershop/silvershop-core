<?php

namespace SilverShop\Model;

use SilverStripe\ORM\DataObject;

/**
 * A single line in an order. This could be an item, or a subtotal line.
 *
 * @see OrderItem
 * @see OrderModifier
 *
 * @property float $CalculatedTotal
 * @property int $OrderID
 * @method   Order Order()
 */
class OrderAttribute extends DataObject
{
    private static string $singular_name = 'Attribute';

    private static string $plural_name = 'Attributes';

    private static array $db = [
        'CalculatedTotal' => 'Currency',
    ];

    private static array $has_one = [
        'Order' => Order::class,
    ];

    private static array $casting = [
        'TableTitle' => 'Text',
        'CartTitle' => 'Text',
    ];

    private static string $table_name = 'SilverShop_OrderAttribute';

    public function canCreate($member = null, $context = []): bool
    {
        return false;
    }

    public function canDelete($member = null): bool
    {
        return false;
    }

    public function isLive(): bool
    {
        if (!$this->isInDB()) {
            return true;
        }
        return $this->Order()->exists() && $this->Order()->IsCart();
    }

    /**
     * Produces a title for use in templates.
     */
    public function getTableTitle(): string
    {
        $title = $this->i18n_singular_name();
        $this->extend('updateTableTitle', $title);
        return $title;
    }

    public function getCartTitle(): string
    {
        $title = $this->getTableTitle();
        $this->extend('updateCartTitle', $title);
        return $title;
    }

    public function ShowInTable(): bool
    {
        $showInTable = true;
        $this->extend('updateShowInTable', $showInTable);
        return $showInTable;
    }
}

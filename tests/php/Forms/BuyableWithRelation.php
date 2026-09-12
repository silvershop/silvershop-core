<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use SilverShop\Model\Buyable;
use SilverShop\Model\OrderItem;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * A custom buyable that is NOT variation-capable but DOES declare an unrelated
 * has_many relation.
 *
 * This is the shape that trips {@see \SilverShop\Forms\CartEditField}: the field
 * used to guard variation rendering with `$buyable->hasMany('Variations')`, but
 * {@see DataObject::hasMany()} takes a `$classOnly` flag — not a component name —
 * so any non-empty has_many made the guard truthy and led to a call to the
 * non-existent `Variations()` method.
 */
class BuyableWithRelation extends DataObject implements Buyable, TestOnly
{
    private static array $db = [
        'Title' => 'Varchar',
        'Price' => 'Currency',
    ];

    // An unrelated has_many — enough to make the buggy hasMany('Variations')
    // guard return truthy even though this buyable has no Variations relation.
    private static array $has_many = [
        'Notes' => BuyableWithRelation_Note::class,
    ];

    private static string $order_item = BuyableWithRelation_OrderItem::class;

    private static string $table_name = 'SilverShop_Test_BuyableWithRelation';

    public function createItem(int $quantity = 1, array $filter = []): OrderItem
    {
        $item = Injector::inst()->create(BuyableWithRelation_OrderItem::class);
        $item->BuyableWithRelationID = $this->ID;
        $item->Quantity = $quantity;

        if ($filter !== []) {
            $item->update($filter);
        }

        return $item;
    }

    public function canPurchase(?Member $member = null, int $quantity = 1): bool
    {
        return $this->Price > 0;
    }

    public function sellingPrice(): float
    {
        return $this->Price;
    }
}

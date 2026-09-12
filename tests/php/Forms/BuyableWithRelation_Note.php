<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Child of {@see BuyableWithRelation} — exists only to give the buyable a
 * non-empty has_many relation for the CartEditField regression test.
 */
class BuyableWithRelation_Note extends DataObject implements TestOnly
{
    private static array $db = [
        'Body' => 'Text',
    ];

    private static array $has_one = [
        'BuyableWithRelation' => BuyableWithRelation::class,
    ];

    private static string $table_name = 'SilverShop_Test_BuyableWithRelation_Note';
}

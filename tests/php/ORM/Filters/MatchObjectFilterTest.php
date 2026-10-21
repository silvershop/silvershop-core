<?php

declare(strict_types=1);

namespace SilverShop\Tests\ORM\Filters;

use SilverShop\Model\Product\OrderItem;
use SilverShop\ORM\Filters\MatchObjectFilter;
use SilverStripe\Dev\SapphireTest;

final class MatchObjectFilterTest extends SapphireTest
{
    public function testRelationId(): void
    {
        // Tests that an ID is automatically added to any relation fields in the DataObject's has_one.
        $matchObjectFilter = new MatchObjectFilter(OrderItem::class, ['ProductID' => 5], ['ProductID']);
        $this->assertEquals($matchObjectFilter->getFilter(), ['"ProductID" = \'5\''], 'ID was added to filter');
    }

    public function testMissingValues(): void
    {
        // Tests that missing values are included in the filter as IS NULL or = 0
        // Missing value for a has_one relationship field.
        $filter = new MatchObjectFilter(OrderItem::class, [], ['ProductID']);
        $this->assertEquals(
            $filter->getFilter(),
            ['("ProductID" = 0 OR "ProductID" IS NULL)'],
            'missing ID value matches 0 or NULL'
        );
        // Missing value for a db field.
        $filter = new MatchObjectFilter(OrderItem::class, [], ['ProductVersion']);
        $this->assertEquals(
            $filter->getFilter(),
            ['"ProductVersion" IS NULL'],
            'missing DB value became IS NULL'
        );
    }

    public function testRelationNameIsNormalised(): void
    {
        // has_one relations can be listed without the ID suffix, in required fields and in data keys.
        $filter = new MatchObjectFilter(OrderItem::class, ['ProductID' => 5], ['Product']);
        $this->assertEquals(['"ProductID" = \'5\''], $filter->getFilter(), 'required relation name matched');

        $filter = new MatchObjectFilter(OrderItem::class, ['Product' => 5], ['ProductID']);
        $this->assertEquals(['"ProductID" = \'5\''], $filter->getFilter(), 'relation name data key matched');

        $filter = new MatchObjectFilter(OrderItem::class, ['Product' => 4, 'ProductID' => 5], ['Product']);
        $this->assertEquals(['"ProductID" = \'5\''], $filter->getFilter(), 'explicit ID data key wins');
    }

    public function testNormaliseFieldName(): void
    {
        $this->assertSame('ProductID', MatchObjectFilter::normaliseFieldName(OrderItem::class, 'Product'));
        $this->assertSame('ProductID', MatchObjectFilter::normaliseFieldName(OrderItem::class, 'ProductID'));
        $this->assertSame('Quantity', MatchObjectFilter::normaliseFieldName(OrderItem::class, 'Quantity'));
    }
}

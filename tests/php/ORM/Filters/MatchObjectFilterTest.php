<?php

namespace SilverShop\Tests\ORM\Filters;

use SilverShop\Model\Product\OrderItem;
use SilverShop\ORM\Filters\MatchObjectFilter;
use SilverStripe\Dev\SapphireTest;

class MatchObjectFilterTest extends SapphireTest
{
    public function testRelationId()
    {
        // Tests that an ID is automatically added to any relation fields in the DataObject's has_one.
        $filter = new MatchObjectFilter(OrderItem::class, ['ProductID' => 5], ['ProductID']);
        $this->assertEquals($filter->getFilter(), ['"ProductID" = \'5\''], 'ID was added to filter');
    }

    public function testMissingValues()
    {
        // Tests that missing values are included in the filter as IS NULL or = 0
        // Missing value for a has_one relationship field.
        $filter = new MatchObjectFilter(OrderItem::class, [], ['ProductID']);
        $this->assertEquals(
            $filter->getFilter(),
            ['"ProductID" IS NULL'],
            'missing ID value became IS NULL'
        );
        // Missing value for a db field.
        $filter = new MatchObjectFilter(OrderItem::class, [], ['ProductVersion']);
        $this->assertEquals(
            $filter->getFilter(),
            ['"ProductVersion" IS NULL'],
            'missing DB value became IS NULL'
        );
    }
}

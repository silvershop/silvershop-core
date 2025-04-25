<?php

namespace SilverShop\Reports\SideReport;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataList;
use SilverStripe\Reports\Report;

class HeavyProducts extends Report
{
    public function title(): string
    {
        return _t('SilverShop\Reports\SideReport.Heavy', 'Heavy Products');
    }

    public function group(): string
    {
        return _t('SilverShop\Reports\SideReport.ShopGroup', 'Shop');
    }

    public function sort(): int
    {
        return 0;
    }

    public function sourceRecords($params = null): DataList
    {
        return Product::get()->filter('Weight:GreaterThan', 10)->sort('Weight', 'ASC');
    }

    public function columns(): array
    {
        return [
            'Title' => [
                'title' => 'Title',
                'link' => true,
            ],
            'Weight' => [
                'title' => 'Weight',
            ],
        ];
    }
}

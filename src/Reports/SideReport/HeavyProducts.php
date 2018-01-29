<?php

namespace SilverShop\Core\Reports\SideReport;

use SilverShop\Core\Product\Product;
use SilverStripe\Reports\Report;

class HeavyProducts extends Report
{
    public function title()
    {
        return _t('ShopSideReport.Heavy', 'Heavy Products');
    }

    public function group()
    {
        return _t('ShopSideReport.ShopGroup', 'Shop');
    }

    public function sort()
    {
        return 0;
    }

    public function sourceRecords($params = null)
    {
        return Product::get()->filter('Weight:GreaterThan', 10)->sort('Weight', 'ASC');
    }

    public function columns()
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

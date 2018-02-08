<?php

namespace SilverShop\Reports\SideReport;

use SilverShop\Page\Product;
use SilverStripe\Reports\Report;

class HeavyProducts extends Report
{
    public function title()
    {
        return _t('SilverShop\Reports\SideReport.Heavy', 'Heavy Products');
    }

    public function group()
    {
        return _t('SilverShop\Reports\SideReport.ShopGroup', 'Shop');
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

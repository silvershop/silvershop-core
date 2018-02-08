<?php

namespace SilverShop\Reports\SideReport;

use SilverShop\Page\Product;
use SilverStripe\Reports\Report;

/**
 * All Products Report
 *
 * @subpackage reports
 */
class AllProducts extends Report
{
    public function title()
    {
        return _t('SilverShop\Reports\SideReport.AllProducts', 'All Products');
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
        return Product::get()->sort('Title');
    }

    public function columns()
    {
        return [
            'Title' => [
                'title' => 'Title',
                'link' => true,
            ],
        ];
    }
}

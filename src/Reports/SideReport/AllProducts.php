<?php

namespace SilverShop\Core\Reports\SideReport;

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
        return _t('ShopSideReport.AllProducts', "All Products");
    }

    public function group()
    {
        return _t('ShopSideReport.ShopGroup', "Shop");
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
        return array(
            "Title" => array(
                "title" => "Title",
                "link"  => true,
            ),
        );
    }
}
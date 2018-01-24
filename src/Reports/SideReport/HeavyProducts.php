<?php

namespace SilverShop\Core\Reports\SideReport;

use SilverStripe\Reports\Report;

class HeavyProducts extends Report
{
    public function title()
    {
        return _t('ShopSideReport.Heavy', "Heavy Products");
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
        return Product::get()->where("\"Product\".\"Weight\" > 10")->sort("\"Weight\" ASC");
    }

    public function columns()
    {
        return array(
            "Title" => array(
                "title" => "Title",
                "link"  => true,
            ),
            "Weight" => array(
                'title' => 'Weight',
            ),
        );
    }
}

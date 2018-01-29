<?php

namespace SilverShop\Core\Reports\SideReport;

use SilverShop\Core\Product\Product;
use SilverStripe\Reports\Report;

class NoImageProducts extends Report
{
    public function title()
    {
        return _t('ShopSideReport.NoImage', 'Products with no image');
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
        return Product::get()->filter('ImageID', 0)->sort('Title', 'ASC');
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

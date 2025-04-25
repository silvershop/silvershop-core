<?php

namespace SilverShop\Reports\SideReport;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataList;
use SilverStripe\Reports\Report;

/**
 * All Products Report
 *
 * @subpackage reports
 */
class AllProducts extends Report
{
    public function title(): string
    {
        return _t('SilverShop\Reports\SideReport.AllProducts', 'All Products');
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
        return Product::get()->sort('Title');
    }

    public function columns(): array
    {
        return [
            'Title' => [
                'title' => 'Title',
                'link' => true,
            ],
        ];
    }
}

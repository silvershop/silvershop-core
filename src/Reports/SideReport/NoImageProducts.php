<?php

namespace SilverShop\Reports\SideReport;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataList;
use SilverStripe\Reports\Report;

class NoImageProducts extends Report
{
    public function title(): string
    {
        return _t('SilverShop\Reports\SideReport.NoImage', 'Products with no image');
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
        return Product::get()->filter('ImageID', 0)->sort('Title', 'ASC');
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

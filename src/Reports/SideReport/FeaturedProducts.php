<?php

namespace SilverShop\Reports\SideReport;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataList;
use SilverStripe\Reports\Report;

/**
 * Shop Side Report classes are to allow quick reports that can be accessed
 * on the Reports tab to the left inside the SilverStripe CMS.
 * Currently there are reports to show products flagged as 'FeatuedProduct',
 * as well as a report on all products within the system.
 *
 * @package    shop
 * @subpackage reports
 */
class FeaturedProducts extends Report
{
    public function title(): string
    {
        return _t('SilverShop\Reports\SideReport.FeaturedProducts', 'Featured Products');
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
        return Product::get()->filter('Featured', 1)->sort('Title');
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

<?php

/**
 * Shop Side Report classes are to allow quick reports that can be accessed
 * on the Reports tab to the left inside the SilverStripe CMS.
 * Currently there are reports to show products flagged as 'FeatuedProduct',
 * as well as a report on all products within the system.
 *
 * @package    shop
 * @subpackage reports
 */
class ShopSideReport_FeaturedProducts extends SS_Report
{
    public function title()
    {
        return _t('ShopSideReport.FeaturedProducts', "Featured Products");
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
        return Product::get()->filter('Featured', 1)->sort("Title");
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

/**
 * All Products Report
 *
 * @subpackage reports
 */
class ShopSideReport_AllProducts extends SS_Report
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

class ShopSideReport_NoImageProducts extends SS_Report
{
    public function title()
    {
        return _t('ShopSideReport.NoImage', "Products with no image");
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
        return Product::get()->where("\"Product\".\"ImageID\" IS NULL OR \"Product\".\"ImageID\" <= 0")->sort(
            "\"Title\" ASC"
        );
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

class ShopSideReport_HeavyProducts extends SS_Report
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

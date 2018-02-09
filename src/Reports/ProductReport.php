<?php

namespace SilverShop\Reports;

use SilverShop\Page\Product;
use SilverStripe\CMS\Model\SiteTree;

class ProductReport extends ShopPeriodReport
{
    protected $title = 'Products';

    protected $description = 'Understand which products are performing, and which aren\'t.';

    protected $dataClass = Product::class;

    protected $periodfield = '"SiteTree"."Created"';

    public function columns()
    {
        return array(
            'Title' => array(
                'title' => 'Title',
                'formatting' => '<a href=\"admin/catalog/Product/EditForm/field/Product/item/$ID/edit\" target=\"_new\">$Title</a>',
            ),
            'BasePrice' => 'Price',
            'Created' => 'Created',
            'Quantity' => 'Quantity',
            'Sales' => 'Sales',
        );
    }

    public function query($params)
    {
        $query = parent::query($params);
        $query->selectField($this->periodfield, 'FilterPeriod')
            ->addSelect(
                [
                '"SilverShop_Product"."ID"',
                '"SiteTree"."ClassName"',
                '"SiteTree"."Title"',
                '"SilverShop_Product"."BasePrice"',
                '"SiteTree"."Created"',
                ]
            )
            ->selectField('COUNT("SilverShop_OrderItem"."Quantity")', 'Quantity')
            ->selectField('SUM("SilverShop_OrderAttribute"."CalculatedTotal")', 'Sales');
        $query->addInnerJoin('SiteTree', '"SilverShop_Product"."ID" = "SiteTree"."ID"');
        $query->addLeftJoin('SilverShop_Product_OrderItem', 'SilverShop_Product.ID = "SilverShop_Product_OrderItem"."ProductID"');
        $query->addLeftJoin('SilverShop_OrderItem', '"SilverShop_Product_OrderItem"."ID" = "SilverShop_OrderItem"."ID"');
        $query->addLeftJoin('SilverShop_OrderAttribute', '"SilverShop_Product_OrderItem"."ID" = "SilverShop_OrderAttribute"."ID"');
        $query->addLeftJoin('SilverShop_Order', '"SilverShop_OrderAttribute"."OrderID" = "SilverShop_Order"."ID"');
        $query->addGroupby('"SilverShop_Product"."ID"');
        $query->addWhere('"SilverShop_Order"."Paid" IS NOT NULL OR "SilverShop_Product_OrderItem"."ID" IS NULL');

        return $query;
    }
}

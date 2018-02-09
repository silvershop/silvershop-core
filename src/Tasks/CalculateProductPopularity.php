<?php

namespace SilverShop\Tasks;

use SilverShop\Page\Product;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

class CalculateProductPopularity extends BuildTask
{
    protected $title = 'Calculate Product Sales Popularity';

    protected $description = 'Count up total sales quantites for each product';

    private static $number_sold_calculation_type = 'SUM'; //SUM or COUNT

    public function run($request)
    {
        if ($request->getVar('via') == 'php') {
            $this->viaphp();
        } else {
            $this->viasql();
        }
        echo 'product sales counts updated';
    }

    /**
     * Update both live and stage tables, based on the algorithm:
     *    product popularity = sum(1/order_age) * sum(item_quantity)
     */
    public function viasql()
    {
        foreach (array('_Live', '') as $stage) {
            $sql = <<<SQL
UPDATE "SilverShop_Product$stage" SET "Popularity" = (
  SELECT
    SUM(1 / (DATEDIFF(NOW(),"SilverShop_Order"."Paid")+1)) * SUM("SilverShop_OrderItem"."Quantity")
    #  / DATEDIFF("SiteTree$stage"."Created",NOW())
    AS Popularity
  FROM "SiteTree$stage"
    INNER JOIN "SilverShop_Product_OrderItem" ON "SiteTree$stage"."ID" = "SilverShop_Product_OrderItem"."ProductID"
    INNER JOIN "SilverShop_OrderItem" ON "SilverShop_OrderItem"."ID" = "SilverShop_Product_OrderItem"."ID"
    INNER JOIN "SilverShop_OrderAttribute" ON "SilverShop_OrderItem"."ID" = "SilverShop_OrderAttribute"."ID"
    INNER JOIN "SilverShop_Order" ON "SilverShop_OrderAttribute"."OrderID" = "SilverShop_Order"."ID"
  WHERE "SiteTree$stage"."ID" = "SilverShop_Product$stage"."ID"
    AND "SilverShop_Order"."Paid" IS NOT NULL
  GROUP BY "SilverShop_Product$stage"."ID"
);
SQL;
            DB::query($sql);
        }
    }

    //legacy function  for working out popularity
    public function viaphp()
    {
        $ps = singleton(Product::class);
        $q = $ps->buildSQL('"SilverShop_Product"."AllowPurchase" = 1');
        $select = $q->select;
        $select['NewPopularity'] =
            self::config()->number_sold_calculation_type . '("SilverShop_OrderItem"."Quantity") AS "NewPopularity"';
        $q->select($select);
        $q->groupby('"Product"."ID"');
        $q->orderby('"NewPopularity" DESC');
        $q->leftJoin('SilverShop_Product_OrderItem', '"SilverShop_Product"."ID" = "SilverShop_Product_OrderItem"."ProductID"');
        $q->leftJoin('SilverShop_OrderItem', '"SilverShop_Product_OrderItem"."ID" = "SilverShop_OrderItem"."ID"');
        $records = $q->execute();
        $productssold = $ps->buildDataObjectSet($records, "DataObjectSet", $q, Product::class);
        //TODO: this could be done faster with an UPDATE query (SQLQuery doesn't support this yet @11/06/2010)
        foreach ($productssold as $product) {
            if ($product->NewPopularity != $product->Popularity) {
                $product->Popularity = $product->NewPopularity;
                $product->writeToStage('Stage');
                $product->publishSingle();
            }
        }
    }
}

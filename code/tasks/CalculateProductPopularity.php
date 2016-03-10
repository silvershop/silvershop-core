<?php

class CalculateProductPopularity extends BuildTask
{
    protected      $title                        = "Calculate Product Sales Popularity";

    protected      $description                  = "Count up total sales quantites for each product";

    private static $number_sold_calculation_type = "SUM"; //SUM or COUNT

    public function run($request)
    {
        if ($request->getVar('via') == "php") {
            $this->viaphp();
        } else {
            $this->viasql();
        }
        echo "product sales counts updated";
    }

    /**
     * Update both live and stage tables, based on the algorithm:
     *    product popularity = sum(1/order_age) * sum(item_quantity)
     */
    public function viasql()
    {
        foreach (array("_Live", "") as $stage) {
            $sql = <<<SQL
				UPDATE "Product$stage" SET "Popularity" = (
					SELECT
						SUM(1 / (DATEDIFF(NOW(),Order.Paid)+1)) * SUM(`OrderItem`.`Quantity`)
						#	/ DATEDIFF(SiteTree$stage.Created,NOW())
						AS Popularity
					FROM "SiteTree$stage"
						INNER JOIN "Product_OrderItem" ON "SiteTree$stage"."ID" = "Product_OrderItem"."ProductID"
						INNER JOIN "OrderItem" ON "OrderItem"."ID" = "Product_OrderItem"."ID"
						INNER JOIN "OrderAttribute" ON "OrderItem"."ID" = "OrderAttribute"."ID"
						INNER JOIN "Order" ON "OrderAttribute"."OrderID" = "Order"."ID"
					WHERE "SiteTree$stage"."ID" = "Product$stage"."ID"
						AND "Order"."Paid" IS NOT NULL
					GROUP BY "Product$stage"."ID"
				);
SQL;
            DB::query($sql);
        }
    }

    //legacy function  for working out popularity
    public function viaphp()
    {
        $ps = singleton('Product');
        $q = $ps->buildSQL("\"Product\".\"AllowPurchase\" = 1");
        $select = $q->select;
        $select['NewPopularity'] =
            self::config()->number_sold_calculation_type . "(\"OrderItem\".\"Quantity\") AS \"NewPopularity\"";
        $q->select($select);
        $q->groupby("\"Product\".\"ID\"");
        $q->orderby("\"NewPopularity\" DESC");
        $q->leftJoin('Product_OrderItem', '"Product"."ID" = "Product_OrderItem"."ProductID"');
        $q->leftJoin('OrderItem', '"Product_OrderItem"."ID" = "OrderItem"."ID"');
        $records = $q->execute();
        $productssold = $ps->buildDataObjectSet($records, "DataObjectSet", $q, 'Product');
        //TODO: this could be done faster with an UPDATE query (SQLQuery doesn't support this yet @11/06/2010)
        foreach ($productssold as $product) {
            if ($product->NewPopularity != $product->Popularity) {
                $product->Popularity = $product->NewPopularity;
                $product->writeToStage('Stage');
                $product->publish('Stage', 'Live');
            }
        }
    }
}

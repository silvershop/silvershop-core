<?php

class CalculateProductPopularity extends BuildTask{

	protected $title = "Calculate Product Sales Popularity";
	protected $description = "Count up total sales quantites for each product";
	
	function run($request){
		//sum quantities for each product, where order is paid
		$sql =<<<SQL
			UPDATE "Product" SET "NumberSold" = (
				SELECT Sum("OrderItem"."Quantity") AS Quantity
				FROM "Product_OrderItem"
					INNER JOIN "OrderItem" ON "OrderItem"."ID" = "Product_OrderItem"."ID"
					INNER JOIN "OrderAttribute" ON "OrderItem"."ID" = "OrderAttribute"."ID"
					INNER JOIN "Order" ON "OrderAttribute"."OrderID" = "Order"."ID"
				WHERE "Product_OrderItem"."ProductID" = "Product"."ID"
					AND "Order"."Paid" IS NOT NULL
				GROUP BY "Product"."ID"
			);
SQL;
	
		DB::query($sql);
		echo "product sales counts updated";
	}
	
}
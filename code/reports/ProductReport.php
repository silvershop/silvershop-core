<?php

class ProductReport extends ShopPeriodReport{
	
	protected $title = "Products";
	protected $description = "Understand which products are performing, and which aren't.";
	protected $dataClass = "Product";
	protected $periodfield = "SiteTree.Created";
		
	function getReportField(){
		$reportfield = parent::getReportField();
		$reportfield->setShowPagination(true);
		return $reportfield;
	}
	
	function columns(){
		return array(
			"Title" => array(
				"title" => "Title",
				"formatting" => '<a href=\"admin/products/Product/$ID/edit\" target=\"_new\">$Title</a>'
			),
			"BasePrice" => "Price",
			"Created" => "Created",
			"Quantity" => "Quantity",
			"Sales" => "Sales"
		);
	}
	
	function sourceQuery($params){
		$query = parent::query($params);
		$query->select(
			"$this->periodfield AS FilterPeriod",
			"Product.ID",
			"SiteTree.ClassName",
			"SiteTree.Title",
			"Product.BasePrice",
			"SiteTree.Created",
			"Count(OrderItem.Quantity) AS Quantity",
			"Sum(OrderAttribute.CalculatedTotal) AS Sales"
		);
		$query->innerJoin("SiteTree","Product.ID = SiteTree.ID");
		$query->leftJoin("Product_OrderItem","Product.ID = Product_OrderItem.ProductID");
		$query->leftJoin("OrderItem","Product_OrderItem.ID = OrderItem.ID");
		$query->leftJoin("OrderAttribute","Product_OrderItem.ID = OrderAttribute.ID");
		$query->leftJoin("Order","OrderAttribute.OrderID = Order.ID");
		$query->groupby("Product.ID");
		$query->where("\"Order\".\"Paid\" IS NOT NULL OR \"Product_OrderItem\".\"ID\" IS NULL");
		if(!$query->orderby){
			$query->orderby("Sales DESC");
		}
		return $query;
	}
	
}
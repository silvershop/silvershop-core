<?php

class DeleteEcommerceProducts extends BuildTask{
	
	protected $title = "Delete eCommerce Products";
	
	protected $description = "Removes all Products from the database.";
	
	function run($request){
		
		
		 //TODO: include decendant clases..incase some subclassing has been done somewhere
		
		if($allorders = DataObject::get('Order')){
			foreach($allorders as $order){
				$order->delete();
				$order->destroy();
			}
		}
		
		if($allproducts = DataObject::get('Product')){
			
			foreach($allproducts as $product){
				$product->delete();
				$product->destroy();
				//TODO: remove versions		
			}
			
		}
		
		//TODO: use TRUNCATE instead?
		
		$tablestoempty = array(
			'Product',
				'Product_Live','Product_versions','Product_ProductGroups','Product_OrderItem','Product_VariationAttributes',
			'ProductVariation',
				'ProductVariation_AttributeValues','ProductVariation_OrderItem','ProductVariation_versions',
			'ProductAttributeType','ProductAttributeValue',
			'Order',
			'OrderAttribute',
				'OrderItem','OrderModifier',
			//TODO: shipping modifiers	
				
			'OrderStatusLog',
			
			//TODO: Payments
		);
		
		foreach($tablestoempty as $table){
			//TODO: check it is a class before attempting
			DB::query("DELETE FROM \"$table\" WHERE 1;");
			echo "<p>Deleting all $table</p>";
		}
		
		//partial empty queries
		DB::query("DELETE FROM \"SiteTree\" WHERE ClassName = 'Product';");//SiteTree
		DB::query("DELETE FROM \"SiteTree\" WHERE ClassName = 'Product';");//SiteTree

		
	}
	
}
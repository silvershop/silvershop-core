<?php

class ProductVariationsFromAttributeCombinations extends CliController{

	function process(){

		$products = DataObject::get('Product');
		if(!$products) return;

		foreach($products as $product){
			$product->generateVariationsFromAttributes();
		}

	}

}


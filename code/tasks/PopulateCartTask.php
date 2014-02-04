<?php

/**
 * Add 5 random Live products to cart, with random quantities between 1 and 10.
 */
class PopulateCartTask extends BuildTask{

	protected $title = "Populate Cart";
	protected $description = "Add 5 random Live products or variations to cart, with random quantities between 1 and 10.";

	public function run($request){
		$cart = ShoppingCart::singleton();
		if($products = Versioned::get_by_stage("Product", "Live","","RAND()","",5)){
			foreach($products as $product){
				$variations = $product->Variations();
				if($variations->exists()){
					$product = $variations->sort("RAND()")->first();
				}
				$cart->add($product,(int)rand(1, 10));
			}
		}
		Controller::curr()->redirect(
			Controller::join_links(Director::baseURL(),'checkout')
		);
	}

}

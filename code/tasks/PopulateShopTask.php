<?php
/**
 * Populate shop task
 * 
 * @todo Ideally this task should make use of Spyc, and a single Pages yml file
 * instead of the YamlFixture class, which is intended for testing.
 * 
 * @package shop
 * @subpackage tasks
 */
class PopulateShopTask extends BuildTask{
	
	protected $title = "Populate Shop";
	protected $description = 'Creates dummy account page, products, checkout page, terms page.';
	
	function run($request){
		
		$this->extend("beforePopulate");
		
		//create products
		if(!DataObject::get_one('Product')){
			$fixture = new YamlFixture(SHOP_DIR."/tests/fixtures/dummyproducts.yml");
			$fixture->saveIntoDatabase();
			$categoriestopublish = array(
				'products',
					'electronics',
					'apparel',
					'entertainment',
						'music',
						'movies',
							'drama',
					'toys',
					'food',
					'books',
					'jewellery',
					'furniture',
						'kitchen',
						'bedroom',
					'stationery'
			);
			foreach($categoriestopublish as $categoryname){
				$fixture->objFromFixture("ProductCategory", $categoryname)->publish('Stage','Live');
			}
			$productstopublish = array(
				'mp3player', 'hdtv',
				'socks', 'tshirt', 
				'beachball','hoop','kite',
				'genericmovie',
				'lemonchicken',
				'ring',
				'book',
				'lamp',
				'paper','pens'
			);
			foreach($productstopublish as $productname){
				$fixture->objFromFixture("Product", $productname)->publish('Stage','Live');
			}
			DB::alteration_message('Created dummy products and categories', 'created');
		}else{
			echo "<p style=\"color:orange;\">Products and categories were not created because some already exist.</p>";
		}
				
		//terms page
		if(!$termsPage = DataObject::get_one('Page', "\"URLSegment\" = 'terms-and-conditions'")) {
			$fixture = new YamlFixture(SHOP_DIR."/tests/fixtures/pages/TermsConditions.yml");
			$fixture->saveIntoDatabase();
			$page = $fixture->objFromFixture("Page", "termsconditions");
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			//set terms page id in config
			$config = SiteConfig::current_site_config();
			$config->TermsPageID = $page->ID;
			$config->write();
			DB::alteration_message("Terms and conditions page created", 'changed');
		}
		
		//cart page
		if(!$page = DataObject::get_one('CartPage')) {
			$fixture = new YamlFixture(SHOP_DIR."/tests/fixtures/pages/Cart.yml");
			$fixture->saveIntoDatabase();
			$page = $fixture->objFromFixture("CartPage", "cart");
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Cart page created', 'created');
		}
		
		//checkout page
		if(!$page = DataObject::get_one('CheckoutPage')) {
			$fixture = new YamlFixture(SHOP_DIR."/tests/fixtures/pages/Checkout.yml");
			$fixture->saveIntoDatabase();
			$page = $fixture->objFromFixture("CheckoutPage", "checkout");
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Checkout page created', 'created');
		}

		//account page
		if(!DataObject::get_one('AccountPage')) {
			$fixture = new YamlFixture(SHOP_DIR."/tests/fixtures/pages/Account.yml");
			$fixture->saveIntoDatabase();
			$page = $fixture->objFromFixture("AccountPage", "account");
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Account page \'Account\' created', 'created');
		}
		
		$this->extend("afterPopulate");
	}
	
}
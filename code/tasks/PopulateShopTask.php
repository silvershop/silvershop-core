<?php
/**
 * 
 * 
 * @author jeremy
 * @package shop
 * @subpackage tasks
 */
class PopulateShopTask extends BuildTask{
	
	protected $title = "Populate Shop";
	protected $description = 'Creates dummy account page, products, checkout page, terms page.';
	
	function run($request){
		//create products
		if(!DataObject::get_one('Product')){
			$fixture = new YamlFixture(SHOP_DIR."/tests/dummyproducts.yml");
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
		
		//cart page
		if(!$page = DataObject::get_one('CartPage')) {
			$page = new CartPage();
			$page->Title = _t('CartPage.Title',"Cart");
			$page->URLSegment = 'cart';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Cart page created', 'created');
		}
		
		//terms page
		if($page->TermsPageID == 0 && $termsPage = DataObject::get_one('Page', "\"URLSegment\" = 'terms-and-conditions'")) {
			$page->TermsPageID = $termsPage->ID;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message("Page '{$termsPage->Title}' linked to the Checkout page '{$page->Title}'", 'changed');
		}
		
		//checkout page
		if(!$page = DataObject::get_one('CheckoutPage')) {
			$page = new CheckoutPage();
			$page->Title = _t('CheckoutPage.Title',"Checkout");
			$page->Content = '<p>This is the checkout page. The order summary and order form appear below this content.</p>';
			$page->PurchaseComplete = '<p>Your purchase is complete.</p>';
			$page->ChequeMessage = '<p>Please note: Your goods will not be dispatched until we receive your payment.</p>';
			$page->URLSegment = 'checkout';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Checkout page created', 'created');
		}

		//account page
		if(!DataObject::get_one('AccountPage')) {
			$page = new AccountPage();
			$page->Title = 'Account';
			$page->Content = '<p>This is the account page. It is used for shop users to login and change their member details if they have an account.</p>';
			$page->URLSegment = 'account';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
			DB::alteration_message('Account page \'Account\' created', 'created');
		}
	}
	
}
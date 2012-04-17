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
		
		//categories
		if(!DataObject::get_one('ProductCategory')) {
			$page1 = new ProductCategory();
			$page1->Title = 'Products';
			$page1->Content = "
						<p>This is the top level products page, it uses the <em>product group</em> page type, and it allows you to show your products checked as 'featured' on it. It also allows you to nest <em>product category</em> pages inside it.</p>
						<p>For example, you have a product category called 'DVDs', and inside you have more product categories like 'sci-fi', 'horrors' or 'action'.</p>
						<p>In this example we have setup a main product category (this page), with a nested product category containing 2 example products.</p>
					";
			$page1->URLSegment = 'products';
			$page1->writeToStage('Stage');
			$page1->publish('Stage', 'Live');
			DB::alteration_message('Product category page \'Products\' created', 'created');
		
			$page2 = new ProductCategory();
			$page2->Title = 'Example product category';
			$page2->Content = '<p>This is a nested <em>product category</em> within the main <em>product category</em> page. You can add a paragraph here to describe what this product category is about, and what sort of products you can expect to find in it.</p>';
			$page2->URLSegment = 'example-product-category';
			$page2->ParentID = $page1->ID;
			$page2->writeToStage('Stage');
			$page2->publish('Stage', 'Live');
			DB::alteration_message('Product category page \'Example product category\' created', 'created');
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
		
		//products
		if(!DataObject::get_one('Product')) {
			if(!DataObject::get_one('ProductCategory')) singleton('ProductCategory')->requireDefaultRecords();
			if($group = DataObject::get_one('ProductCategory', '', true, "\"ParentID\" DESC")) {
				$content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>';
		
				$page1 = new Product();
				$page1->Title = 'Example product';
				$page1->Content = $content . '<p>You may also notice that we have checked it as a featured product and it will be displayed on the main Products page.</p>';
				$page1->URLSegment = 'example-product';
				$page1->ParentID = $group->ID;
				$page1->Price = '15.00';
				$page1->Weight = '0.50';
				$page1->Model = 'Joe Bloggs';
				$page1->FeaturedProduct = true;
				$page1->writeToStage('Stage');
				$page1->publish('Stage', 'Live');
				DB::alteration_message('Product page \'Example product\' created', 'created');
		
				$page2 = new Product();
				$page2->Title = 'Example product 2';
				$page2->Content = $content;
				$page2->URLSegment = 'example-product-2';
				$page2->ParentID = $group->ID;
				$page2->Price = '25.00';
				$page2->Weight = '1.2';
				$page2->Model = 'Jane Bloggs';
				$page2->writeToStage('Stage');
				$page2->publish('Stage', 'Live');
				DB::alteration_message('Product page \'Example product 2\' created', 'created');
			}
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
		
			DB::alteration_message('Checkout page \'Checkout\' created', 'created');
		}
		
		//terms page
		if($page->TermsPageID == 0 && $termsPage = DataObject::get_one('Page', "\"URLSegment\" = 'terms-and-conditions'")) {
			$page->TermsPageID = $termsPage->ID;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');
		
			DB::alteration_message("Page '{$termsPage->Title}' linked to the Checkout page '{$page->Title}'", 'changed');
		}
		
	}
	
}
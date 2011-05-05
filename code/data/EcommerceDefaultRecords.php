<?php


/**
 * @description: cleans up old (abandonned) carts...
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 **/


class EcommerceDefaultRecords extends DatabaseAdmin {

	function run() {

		// ACCOUNT PAGE
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


		//CHECKOUT PAGE

		$update = array();
		if(!$page = DataObject::get_one('CheckoutPage')) {
			$page = new CheckoutPage();
			$page->Content = '<p>This is the checkout page. The order summary and order form appear below this content.</p>';
			$page->MetaTitle = 'Checkout';
			$page->MenuTitle = 'Checkout';
			$page->Title = 'Checkout';
			$update[] = 'Checkout page \'Checkout\' created';
			$page->URLSegment = 'checkout';
			$page->ShowInMenus = 0;
			if($page->TermsPageID == 0 && $termsPage = DataObject::get_one('Page', "\"URLSegment\" = 'terms-and-conditions'")) {
				$page->TermsPageID = $termsPage->ID;
				$update[] = 'added terms page';
			}
		}
		if($page) {
			if(!$page->PurchaseComplete) {$page->PurchaseComplete = '<p>'._t('Checkout.PURCHASECOMPLETE','Your purchase is complete.').'</p>'; $update[] = "added PurchaseComplete content";}
			if(!$page->ChequeMessage) {$page->ChequeMessage = '<p>'._t('Checkout.CHEQUEMESSAGE','Please note: Your goods will not be dispatched until we receive your payment.').'</p>'; $update[] = "added ChequeMessage content";}
			if(!$page->AlreadyCompletedMessage) {$page->AlreadyCompletedMessage = '<p>'._t('Checkout.ALREADYCOMPLETEDMESSAGE','').'This order has already been completed.</p>'; $update[] = "added AlreadyCompletedMessage content";}
			if(!$page->FinalizedOrderLinkLabel) {$page->FinalizedOrderLinkLabel = _t('Checkout.FINALIZEDORDERLINKLABEL','view completed order'); $update[] = "added FinalizedOrderLinkLabel content";}
			if(!$page->CurrentOrderLinkLabel) {$page->CurrentOrderLinkLabel = _t('Checkout.CURRENTORDERLINKLABEL','view current order'); $update[] = "added CurrentOrderLinkLabel content";}
			if(!$page->StartNewOrderLinkLabel) {$page->StartNewOrderLinkLabel = _t('Checkout.STARTNEWDORDERLINKLABEL','start new order'); $update[] = "added StartNewOrderLinkLabel content";}
			if(!$page->NonExistingOrderMessage) {$page->NonExistingOrderMessage = '<p>'._t('Checkout.NONEXISTINGORDERMESSAGE','We can not find your order.').'</p>'; $update[] = "added NonExistingOrderMessage content";}
			if(!$page->NoItemsInOrderMessage) {$page->NoItemsInOrderMessage = '<p>'._t('Checkout.NONITEMSINORDERMESSAGE','There are no items in your order. Please add some products first.').'</p>'; $update[] = "added NoItemsInOrderMessage content";}
			if(!$page->MustLoginToCheckoutMessage) {$page->MustLoginToCheckoutMessage = '<p>'._t('Checkout.MUSTLOGINTOCHECKOUTMESSAGE','You must login to view this order').'</p>'; $update[] = "added MustLoginToCheckoutMessage content";}
			if(!$page->LoginToOrderLinkLabel) {$page->LoginToOrderLinkLabel = '<p>'._t('Checkout.LOGINTOORDERLINKLABEL','log in and view order').'</p>'; $update[] = "added LoginToOrderLinkLabel content";}
			if(count($update)) {
				$page->writeToStage('Stage');
				$page->publish('Stage', 'Live');
				DB::alteration_message("create / updated checkout page: ".implode("<br />", $update), 'created');
			}
		}

	}


	function addproducts() {


		// PRODUCT PAGE

		if(!DataObject::get_one('Product')) {
			if(!DataObject::get_one('ProductGroup')) singleton('ProductGroup')->requireDefaultRecords();
			if($group = DataObject::get_one('ProductGroup', '', true, "\"ParentID\" DESC")) {
				$content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>';

				$page1 = new Product();
				$page1->Title = 'Example product';
				$page1->Content = $content . '<p>You may also notice that we have checked it as a featured product and it will be displayed on the main Products page.</p>';
				$page1->URLSegment = 'example-product';
				$page1->ParentID = $group->ID;
				$page1->Price = '15.00';
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
				$page2->writeToStage('Stage');
				$page2->publish('Stage', 'Live');
				DB::alteration_message('Product page \'Example product 2\' created', 'created');
			}
		}

		// PRODUCT GROUPS
		if(!DataObject::get_one('ProductGroup')) {
			$page1 = new ProductGroup();
			$page1->Title = 'Products';
			$page1->Content = "
				<p>This is the top level products page, it uses the <em>product group</em> page type, and it allows you to show your products checked as 'featured' on it. It also allows you to nest <em>product group</em> pages inside it.</p>
				<p>For example, you have a product group called 'DVDs', and inside you have more product groups like 'sci-fi', 'horrors' or 'action'.</p>
				<p>In this example we have setup a main product group (this page), with a nested product group containing 2 example products.</p>
			";
			$page1->URLSegment = 'products';
			$page1->NumberOfProductsPerPage = 5;
			$page1->writeToStage('Stage');
			$page1->publish('Stage', 'Live');
			DB::alteration_message('Product group page \'Products\' created', 'created');

			$page2 = new ProductGroup();
			$page2->Title = 'Example product group';
			$page2->Content = '<p>This is a nested <em>product group</em> within the main <em>product group</em> page. You can add a paragraph here to describe what this product group is about, and what sort of products you can expect to find in it.</p>';
			$page2->URLSegment = 'example-product-group';
			$page1->NumberOfProductsPerPage = 5;
			$page2->ParentID = $page1->ID;
			$page2->writeToStage('Stage');
			$page2->publish('Stage', 'Live');
			DB::alteration_message('Product group page \'Example product group\' created', 'created');
		}
	}

	/**
	 * This method (removeallorders) is useful when you have placed a whole bunch of practice orders
	 * and you want to go live with the same Database - but without all the practice orders....
	 *
	 **/
	function removeallorders() {

	}
}

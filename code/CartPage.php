<?php

class CartPage extends Page{

	static $db = array();

	static $has_one = array(
		'CheckoutPage' => 'CheckoutPage',
		'ContinuePage' => 'SiteTree'
	);

	static $icon = 'ecommerce/images/icons/cart';

	function getCMSFields(){
		$fields = parent::getCMSFields();
		if($checkouts = DataObject::get('CheckoutPage')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('CheckoutPageID','Checkout Page',$checkouts->toDropDownMap()));
		}
		if($pgroups = DataObject::get('ProductGroup')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('ContinuePageID','Continue Product Group Page',$pgroups->toDropDownMap()));
		}

		return $fields;
	}

}

class CartPage_Controller extends Page_Controller{

		function Order() {
			if($orderID = Director::urlParam('Action')) return DataObject::get_by_id('Order', $orderID);
			else return ShoppingCart::current_order();
		}

		public function init() {
			// include extra js requirements for this page
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript(ECOMMERCE_DIR.'/javascript/CheckoutPage.js');

			// include stylesheet for the checkout page
			Requirements::themedCSS('CheckoutPage');

			parent::init();
	}

}




<?php

class CartPage extends Page{

	public static $db = array();

	public static $has_one = array(
		'CheckoutPage' => 'CheckoutPage',
		'ContinuePage' => 'SiteTree'
	);

	public static $icon = 'ecommerce/images/icons/cart';

	function getCMSFields(){
		$fields = parent::getCMSFields();
		if($checkouts = DataObject::get('CheckoutPage')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('CheckoutPageID','Checkout Page',$checkouts->toDropDownMap()));
		}
		$fields->addFieldToTab('Root.Content.Links',new TreeDropdownField('ContinuePageID','Continue Page',"SiteTree"));
		return $fields;
	}

}

class CartPage_Controller extends Page_Controller{

	public function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('ecommerce/javascript/Cart.js');
		Requirements::themedCSS('CheckoutPage');
	}

	function Order() {
		if($orderID = Director::urlParam('Action')) {
			return DataObject::get_by_id('Order', $orderID);
		}
		else {
			return ShoppingCart::current_order();
		}
	}

}




<?php

/**
 * View and edit the cart in a full page.
 * Visitor can continue shopping, or proceed to checkout.
 */
class CartPage extends Page{

	static $has_one = array(
		'CheckoutPage' => 'CheckoutPage',
		'ContinuePage' => 'SiteTree'
	);

	static $icon = 'shop/images/icons/cart';

	function getCMSFields(){
		$fields = parent::getCMSFields();
		if($checkouts = DataObject::get('CheckoutPage')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('CheckoutPageID','Checkout Page',$checkouts->toDropDownMap()));
		}
		if($pgroups = DataObject::get('ProductCategory')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('ContinuePageID','Continue Product Group Page',$pgroups->toDropDownMap()));
		}
		return $fields;
	}
	
	/**
	 * Returns the link to the checkout page on this site
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	static function find_link($urlSegment = false, $action = null, $id = null) {
		if(!$page = DataObject::get_one('CartPage')) {
			return Controller::join_links(Director::baseURL(),CartPage_Controller::$url_segment);
		}
		$id = ($id)? "/".$id : "";
		return ($urlSegment) ? $page->URLSegment : Controller::join_links($page->Link($action),$id);
	}

}

class CartPage_Controller extends Page_Controller{
	
	static $url_segment = 'cart';
	static $allowed_actions = array();
	
	/**
	 * Display a title if there is no model, or no title.
	 */
	public function Title(){
		if($this->Title)
			return $this->Title;
		return _t('CartPage.TITLE',"Shopping Cart");
	}
	
	public function init(){
		parent::init();
		// include extra js requirements for this page
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(SHOP_DIR.'/javascript/CheckoutPage.js');
	}	
	
	/**
	 * @deprecated use $this->Cart() instead
	 */
	function Order() {
		return $this->Cart();
	}
}
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

	/**
	 * Only allow one cart page
	 */
	function canCreate($member = null) {
		return !CartPage::get()->exists();
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		if($checkouts = DataObject::get('CheckoutPage')) {
			$fields->addFieldToTab('Root.Links',new DropdownField('CheckoutPageID','Checkout Page',$checkouts->map("ID","Title")));
		}
		if($pgroups = DataObject::get('ProductCategory')) {
			$fields->addFieldToTab('Root.Links',new DropdownField('ContinuePageID','Continue Product Group Page',$pgroups->map("ID","Title")));
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
	
	public static $url_segment = 'cart';
	private static $allowed_actions = array(
		"CartForm",
		"updatecart"
	);
	
	/**
	 * Display a title if there is no model, or no title.
	 */
	public function Title(){
		if($this->Title)
			return $this->Title;
		return _t('CartPage.TITLE',"Shopping Cart");
	}

	/**
	 * A form for updating cart items
	 */
	public function CartForm(){
		$cart = $this->Cart();
		if(!$cart){
			return false;
		}
		return new CartForm($this,"CartForm",$cart);
	}

}

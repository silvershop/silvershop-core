<?php
/**
 * CheckoutPage is a CMS page-type that shows the order
 * details to the customer for their current shopping
 * cart on the site. It also lets the customer review
 * the items in their cart, and manipulate them (add more,
 * deduct or remove items completely). The most important
 * thing is that the {@link CheckoutPage_Controller} handles
 * the {@link OrderForm} form instance, allowing the customer
 * to fill out their shipping details, confirming their order
 * and making a payment.
 *
 * @see CheckoutPage_Controller->Order()
 * @see OrderForm
 * @see CheckoutPage_Controller->OrderForm()
 *
 * The CheckoutPage_Controller is also responsible for setting
 * up the modifier forms for each of the OrderModifiers that are
 * enabled on the site (if applicable - some don't require a form
 * for user input). A usual implementation of a modifier form would
 * be something like allowing the customer to enter a discount code
 * so they can receive a discount on their order.
 *
 * @see OrderModifier
 * @see CheckoutPage_Controller->ModifierForms()
 *
 * @package shop
 */
class CheckoutPage extends Page {

	public static $db = array(
		'PurchaseComplete' => 'HTMLText',
		'ChequeMessage' => 'HTMLText',
		'AlreadyCompletedMessage' => 'HTMLText',
		'NonExistingOrderMessage' => 'HTMLText',
		'MustLoginToCheckoutMessage' => 'HTMLText',

		'CheckoutFinishMessage' => 'HTMLText'
	);

	static $icon = 'shop/images/icons/money';

	/**
	 * Returns the link to the checkout page on this site
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	static function find_link($urlSegment = false, $action = null, $id = null) {
		if(!$page = DataObject::get_one('CheckoutPage')) {
			return Controller::join_links(Director::baseURL(),CheckoutPage_Controller::$url_segment);
		}
		$id = ($id)? "/".$id : "";
		return ($urlSegment) ? $page->URLSegment : Controller::join_links($page->Link($action),$id);
	}

	function canCreate($member = null) {
		return !DataObject::get_one("SiteTree", "\"ClassName\" = 'CheckoutPage'");
	}

	/**
	 * Returns the link to the checkout page on this site, using
	 * a specific Order ID that already exists in the database.
	 *
	 * @param int $orderID ID of the {@link Order}
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	static function get_checkout_order_link($orderID, $urlSegment = false) {
		if(!$page = DataObject::get_one('CheckoutPage')) {
			user_error('No CheckoutPage was found. Please create one in the CMS!', E_USER_ERROR);
		}
		return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . $orderID;
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Messages', array(
			new HtmlEditorField('AlreadyCompletedMessage', 'Already Completed - shown when the customer tries to checkout an already completed order', $row = 4),
			new HtmlEditorField('NonExistingOrderMessage', 'Non-existing Order - shown when the customer tries ', $row = 4),
			new HtmlEditorField('MustLoginToCheckoutMessage', 'MustLoginToCheckoutMessage', $row = 4),
			new HtmlEditorField('PurchaseComplete', 'Purchase Complete - included in reciept email, after the customer submits the checkout ', $row = 4),
			new HtmlEditorField('ChequeMessage', 'Cheque Message - shown when a customer selects a delayed payment option (such as a cheque payment) ', $rows = 4)
		));
		return $fields;
	}
	
}

class CheckoutPage_Controller extends Page_Controller {
	
	static $url_segment = "checkout";

	public static $extensions = array(
		'OrderManipulation'
	);

	static $allowed_actions = array(
		'OrderForm',
		'removemodifier'
	);
	
	/**
	 * Display a title if there is no model, or no title.
	 */
	public function Title(){
		if($this->Title)
			return $this->Title;
		return _t('CheckoutPage.TITLE',"Checkout");
	}

	/**
	 * Returns a form allowing a user to enter their
	 * details to checkout their order.
	 *
	 * @return OrderForm object
	 */
	function OrderForm() {
		$cart = $this->Cart(); //see ViewableCart.php
		if(!(bool)$cart){
			return false;
		}
		$form = new OrderForm($this, 'OrderForm');
		$this->data()->extend('updateOrderForm',$form);
		$form->loadDataFrom($cart);
		//prevent fields being populated with relation object class names
		$form->loadDataFrom(array(
			"BillingAddress" => "",
			"ShippingAddress" => ""
		));
		
		//load session data
		if($member = Member::currentUser()){
			$form->loadDataFrom($member->DefaultShippingAddress(),false,singleton('Address')->getFieldMap('Shipping'));
			$form->loadDataFrom($member->DefaultBillingAddress(),false,singleton('Address')->getFieldMap('Billing'));
			$form->loadDataFrom($member);
		}
		if($data = Session::get("FormInfo.{$form->FormName()}.data")){
			$form->loadDataFrom($data);
		}
		return $form;
	}

}
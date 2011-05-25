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
 * @package ecommerce
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

	public static $has_one = array(
		'TermsPage' => 'Page'
	);

	public static $has_many = array();

	public static $many_many = array();

	public static $belongs_many = array();

	public static $defaults = array();

	static $icon = 'ecommerce/images/icons/money';

	static $add_action = 'The Checkout Page';

	/**
	 * Returns the link to the checkout page on this site, using
	 * a specific Order ID that already exists in the database.
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	static function find_link($urlSegment = false, $action = null, $id = null) {
		if(!$page = DataObject::get_one('CheckoutPage')) {
			user_error('No CheckoutPage was found. Please create one in the CMS!', E_USER_ERROR);
		}
		$id = ($id)? "/".$id : "";
		return ($urlSegment) ? $page->URLSegment : $page->Link($action).$id;
	}


	function canCreate() {
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

		$fields->addFieldToTab('Root.Content.TermsAndConditions', new TreeDropdownField('TermsPageID', 'Terms and Conditions Page', 'SiteTree'));

		$fields->addFieldsToTab('Root.Content.Messages', array(
			new HtmlEditorField('AlreadyCompletedMessage', 'Already Completed - shown when the customer tries to checkout an already completed order', $row = 4),
			new HtmlEditorField('NonExistingOrderMessage', 'Non-existing Order - shown when the customer tries ', $row = 4),
			new HtmlEditorField('MustLoginToCheckoutMessage', 'MustLoginToCheckoutMessage', $row = 4),
			new HtmlEditorField('PurchaseComplete', 'Purchase Complete - included in reciept email, after the customer submits the checkout ', $row = 4),
			new HtmlEditorField('ChequeMessage', 'Cheque Message - shown when a customer selects a delayed payment option (such as a cheque payment) ', $rows = 4)
		));

		return $fields;
	}

	/**
	 * This automatically creates a CheckoutPage whenever dev/build
	 * is invoked and there is no page on the site with CheckoutPage
	 * applied to it.
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();

		if(!$page = DataObject::get_one('CheckoutPage')) {
			$page = new CheckoutPage();
			$page->Title = 'Checkout';
			$page->Content = '<p>This is the checkout page. The order summary and order form appear below this content.</p>';
			$page->PurchaseComplete = '<p>Your purchase is complete.</p>';
			$page->ChequeMessage = '<p>Please note: Your goods will not be dispatched until we receive your payment.</p>';
			$page->URLSegment = 'checkout';
			$page->ShowInMenus = 0;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');

			DB::alteration_message('Checkout page \'Checkout\' created', 'created');
		}

		if($page->TermsPageID == 0 && $termsPage = DataObject::get_one('Page', "\"URLSegment\" = 'terms-and-conditions'")) {
			$page->TermsPageID = $termsPage->ID;
			$page->writeToStage('Stage');
			$page->publish('Stage', 'Live');

			DB::alteration_message("Page '{$termsPage->Title}' linked to the Checkout page '{$page->Title}'", 'changed');
		}
 	}
}
class CheckoutPage_Controller extends Page_Controller {

	public function init() {
		if(!class_exists('Payment')) {
			trigger_error('The payment module must be installed for the ecommerce module to function.', E_USER_WARNING);
		}

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('ecommerce/javascript/CheckoutPage.js');
		Requirements::javascript('ecommerce/javascript/ecommerce.js');
		Requirements::themedCSS('CheckoutPage');

		$this->initVirtualMethods();

		parent::init();
	}

	/**
	 * Inits the virtual methods from the name of the modifier forms to
	 * redirect the action method to the form class
	 */
	protected function initVirtualMethods() {
		if($forms = $this->ModifierForms()) {
			foreach($forms as $form) {
				$this->addWrapperMethod($form->Name(), 'getOrderModifierForm');
				self::$allowed_actions[] = $form->Name(); // add all these forms to the list of allowed actions also
			}
		}
	}

	/**
	 * Return a specific {@link OrderModifierForm} by it's name.
	 *
	 * @param string $name The name of the form to return
	 * @return Form
	 */
	protected function getOrderModifierForm($name) {
		if($forms = $this->ModifierForms()) {
			foreach($forms as $form) {
				if($form->Name() == $name) return $form;
			}
		}
	}

	/**
	 * Determine whether the user can checkout the
	 * specified Order ID in the URL, that isn't
	 * paid for yet.
	 *
	 * @return boolean
	 */
	function CanCheckout() {
		if($order = $this->Order()) {
			return !$order->IsPaid();
		}
	}

	/**
	 * Returns either the current order from the shopping cart or
	 * by the specified Order ID in the URL.
	 *
	 * @return Order
	 */
	function Order() {
		if($orderID = Director::urlParam('Action') && is_numeric(Director::urlParam('Action'))) {
			$order = DataObject::get_by_id('Order', $orderID);
			if($order && $order->MemberID == Member::currentUserID()) {
				return $order;
			}
		} else {
			return ShoppingCart::current_order();
		}
	}

	/**
	 * Returns a DataObjectSet of {@link OrderModifierForm} objects. These
	 * forms are used in the OrderInformation HTML table for the user to fill
	 * out as needed for each modifier applied on the site.
	 *
	 * @return DataObjectSet
	 */
	function ModifierForms() {
		return Order::get_modifier_forms($this);
	}

	/**
	 * Returns a form allowing a user to enter their
	 * details to checkout their order.
	 *
	 * @return OrderForm object
	 */
	function OrderForm() {
		$form = new OrderForm($this, 'OrderForm');
		$this->data()->extend('updateOrderForm',$form);
		//load session data
		if($data = Session::get("FormInfo.{$form->FormName()}.data")){
			$form->loadDataFrom($data);
		}

		return $form;
	}

	function OrderFormWithoutShippingAddress() {
		$form = new OrderFormWithoutShippingAddress($this, 'OrderFormWithoutShippingAddress');
		$this->data()->extend('OrderFormWithoutShippingAddress',$form);
		return $form;
	}

	function OrderFormWithShippingAddress() {
		$form = new OrderFormWithShippingAddress($this, 'OrderFormWithShippingAddress');
		$this->data()->extend('OrderFormWithShippingAddress',$form);
		return $form;
	}

	/**
	 * Returns a message explaining why the customer
	 * can't checkout the requested order.
	 *
	 * @return string
	 */
	function Message() {
		$orderID = Director::urlParam('Action');
		$checkoutLink = self::find_link();

		if($memberID = Member::currentUserID()) {
			if($order = DataObject::get_one('Order', "\"Order\".\"ID\" = '$orderID' AND \"Order\".\"MemberID\" = '$memberID'")) {
				return 'You can not checkout this order because it has been already successfully completed. Click <a href="' . $order->Link() . '">here</a> to see it\'s details, otherwise you can <a href="' . $checkoutLink . '">checkout</a> your current order.';
			}
			else {
				return 'You do not have any order corresponding to that ID, so you can\'t checkout this order.';
			}
		}
		else {
			$redirectLink = CheckoutPage::get_checkout_order_link($orderID);
			return 'You can not checkout this order because you are not logged in. To do so, please <a href="Security/login?BackURL=' . $redirectLink . '">login</a> first, otherwise you can <a href="' . $checkoutLink . '">checkout</a> your current order.';
		}
	}
	
	/**
	 * Go here after order has been processed.
	 */
	function finish(){
		Requirements::themedCSS('Order');
		//TODO: make redirecting to account page optional
		
		//otherwise display last completed order(s)
		
		$orderid = Director::urlParam('ID');
		
		//security filter to only allow viewing orders associated with this session, or member id
		$filter = " AND \"SessionID\" = '".session_id()."'";
		$filter .= ($cid = Member::currentUserID()) ? " OR \"MemberID\" = $cid" : "";
		
		$order = DataObject::get_one('Order',"\"ID\"= $orderid AND \"Status\" NOT IN('Cart','AdminCancelled','MemberCancelled')".$filter);
		
		//if no id, then get first of latest orders for member or session id?
		
		//TODO: permission message on failure
		
		$message = $mtype = null;
		if(!$order){
			$message = _t("CheckoutPage.ORDERNOTFOUND","Order could not be found.");
			$mtype = 'bad';
		}
		
		return array(
			'Order' => $order,
			'Message' => $message,
			'MessageType' => $mtype
		);
		
	}


}

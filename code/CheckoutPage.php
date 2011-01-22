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
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class CheckoutPage extends Page {

	public static $db = array(
		'PurchaseComplete' => 'HTMLText',
		'ChequeMessage' => 'HTMLText',
		'AlreadyCompletedMessage' => 'HTMLText',
		'FinalizedOrderLinkLabel' => 'Varchar(255)',
		'CurrentOrderLinkLabel' => 'Varchar(255)',
		'NoItemsInOrderMessage' => 'HTMLText',
		'NonExistingOrderMessage' => 'HTMLText',
		'MustLoginToCheckoutMessage' => 'HTMLText',
		'LoginToOrderLinkLabel' => 'Varchar(255)'
	);

	public static $has_one = array(
		'TermsPage' => 'Page'
	);

	public static $has_many = array();

	public static $many_many = array();

	public static $belongs_many = array();

	public static $defaults = array();

	public static $icon = 'ecommerce/images/icons/checkout';

	protected static $add_shipping_fields = false; //SUM or COUNT
		static function set_add_shipping_fields($v){self::$add_shipping_fields = $v;}
		static function get_add_shipping_fields(){return self::$add_shipping_fields;}


	/**
	 * Returns the link to the checkout page on this site, using
	 * a specific Order ID that already exists in the database.
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	public static function find_link($useURLSegment = false) {
		if(!$page = DataObject::get_one('CheckoutPage')) {
			user_error('No CheckoutPage was found. Please create one in the CMS!', E_USER_ERROR);
		}
		return $page->Link();
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
	public static function get_checkout_order_link($orderID) {
		if(!$page = DataObject::get_one('CheckoutPage')) {
			user_error('No CheckoutPage was found. Please create one in the CMS!', E_USER_ERROR);
		}
		return $page->Link("loadorder"). $orderID . "/";
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Content.TermsAndConditions', new TreeDropdownField('TermsPageID', 'Terms and Conditions Page', 'SiteTree'));
		$fields->addFieldsToTab('Root.Content.Messages', array(
			new HtmlEditorField('AlreadyCompletedMessage', 'Already Completed - shown when the customer tries to checkout an already completed order', $row = 4),
			new TextField('FinalizedOrderLinkLabel', 'Label for the link pointing to a completed order - e.g. click here to view the completed order'),
			new TextField('CurrentOrderLinkLabel', 'Label for the link pointing to the current order - e.g. click here to view current order'),
			new HtmlEditorField('NonExistingOrderMessage', 'Non-existing Order - shown when the customer tries ', $row = 4),
			new HtmlEditorField('NoItemsInOrderMessage', 'No items in order - shown when the customer tries to checkout an order without items.', $row = 4),
			new HtmlEditorField('MustLoginToCheckoutMessage', 'MustLoginToCheckoutMessage', $row = 4),
			new TextField('LoginToOrderLinkLabel', 'Label for the link pointing to the order which requires a log in - e.g. click here to log in and view order'),
			new HtmlEditorField('PurchaseComplete', 'Purchase Complete - included in receipt email, after the customer submits the checkout ', $row = 4),
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
			if(!$page->PurchaseComplete) {$page->PurchaseComplete = '<p>Your purchase is complete.</p>'; $update[] = "added PurchaseComplete content";}
			if(!$page->ChequeMessage) {$page->ChequeMessage = '<p>Please note: Your goods will not be dispatched until we receive your payment.</p>'; $update[] = "added ChequeMessage content";}
			if(!$page->PAlreadyCompletedMessage) {$page->AlreadyCompletedMessage = '<p>This order has already been completed.</p>'; $update[] = "added AlreadyCompletedMessage content";}
			if(!$page->FinalizedOrderLinkLabel) {$page->FinalizedOrderLinkLabel = 'view completed order'; $update[] = "added FinalizedOrderLinkLabel content";}
			if(!$page->CurrentOrderLinkLabel) {$page->CurrentOrderLinkLabel = 'view current order'; $update[] = "added CurrentOrderLinkLabel content";}
			if(!$page->NonExistingOrderMessage) {$page->NonExistingOrderMessage = '<p>We can not find your order.</p>'; $update[] = "added NonExistingOrderMessage content";}
			if(!$page->NoItemsInOrderMessage) {$page->NoItemsInOrderMessage = '<p>There are no items in your order. Please add some products first.</p>'; $update[] = "added NoItemsInOrderMessage content";}
			if(!$page->MustLoginToCheckoutMessage) {$page->MustLoginToCheckoutMessage = '<p>You must login to view this order</p>'; $update[] = "added MustLoginToCheckoutMessage content";}
			if(!$page->LoginToOrderLinkLabel) {$page->LoginToOrderLinkLabel = '<p>log in and view order</p>'; $update[] = "added LoginToOrderLinkLabel content";}
			if(count($update)) {
				$page->writeToStage('Stage');
				$page->publish('Stage', 'Live');
				DB::alteration_message("create / updated checkout page: ".implode("<br />", $update), 'created');
			}
		}
 	}

	function MenuTitle() {
		$count = 0;
		$cart = ShoppingCart::current_order();
		if($cart) {
			if($cart = $this->Cart()) {
				if($items = $cart->Items()) {
					$count = $items->count();
				}
			}
		}
		$v = $this->MenuTitle;
		if($count) {
			$v .= " (".$count.")";
		}
		return $v;
	}

}
class CheckoutPage_Controller extends Page_Controller {

	protected $usefulLinks = null;

	protected $order = null;

	public function init() {
		parent::init();
		if(!class_exists('Payment')) {
			trigger_error('The payment module must be installed for the ecommerce module to function.', E_USER_WARNING);
		}
		$this->order = ShoppingCart::current_order();
		ShoppingCart::add_requirements();
		Requirements::javascript('ecommerce/javascript/EcommercePayment.js');
		Requirements::themedCSS('CheckoutPage');
		$this->initVirtualMethods();
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


	/**
	 * Returns either the current order from the shopping cart or
	 * by the specified Order ID in the URL.
	 *
	 * @return Order
	 */
	function loadorder($request) {
		if($orderID = intval($request->param('ID'))) {
			$this->order = ShoppingCart::load_order($orderID);
			Director::redirect($this->Link());
		}
		return array();
	}
	/**
	 * Determine whether the user can checkout the
	 * specified Order ID in the URL, that isn't
	 * paid for yet.
	 *
	 * @return boolean
	 */
	function CanCheckout() {
		if($this->order) {
			if($this->order->Items() && $this->order->CanEdit()) {
				return true;
			}
		}
	}


	/**
	 * Returns a message explaining why the customer
	 * can't checkout the requested order.
	 *
	 * @return string
	 */
	function Message() {
		$this->usefulLinks = new DataObjectSet();
		$checkoutLink = CheckoutPage::find_link();
		if(!Member::currentUserID() && !$this->order) {
			$redirectLink = CheckoutPage::get_checkout_order_link();
			$this->usefulLinks->push(new ArrayData(array("Title" => $this->LoginToOrderLinkLabel, "Link" => 'Security/login?BackURL='.urlencode($redirectLink))));
			$this->usefulLinks->push(new ArrayData(array("Title" => $this->CurrentOrderLinkLabel, "Link" => $checkoutLink)));
			return $this->MustLoginToCheckoutMessage;
			//'You can not checkout this order because you are not logged in. To do so, please <a href="Security/login?BackURL=' . $redirectLink . '">login</a> first, otherwise you can <a href="' . $checkoutLink . '">checkout</a> your current order.'
		}
		elseif(!$this->order) {
			$this->usefulLinks->push(new arrayData(array("Title" => $this->CurrentOrderLinkLabel, "Link" => $checkoutLink)));
			return $this->NonExistingOrderMessage;
		}
		elseif(!$this->order->Items()) {
			return $this->NoItemsInOrderMessage;
		}

		elseif(!$this->order->CanPay() || !$this->order->CanEdit()) {
			$this->usefulLinks->push(new ArrayData(array("Title" => $this->FinalizedOrderLinkLabel, "Link" => $this->order->Link())));
			$this->usefulLinks->push(new ArrayData(array("Title" => $this->CurrentOrderLinkLabel, "Link" => $checkoutLink)));
			return $this->AlreadyCompletedMessage;
		}

	}

	function UsefulLinks() {
		if($this->usefulLinks && $this->usefulLinks->count()) {
			return $this->usefulLinks;
		}
		return null;
	}
}

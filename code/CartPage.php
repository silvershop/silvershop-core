<?php

/**
 * @description: This is a page that shows the cart content,
 * without "leading to" checking out. That is, there is no "next step" functionality.
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: pages
 *
 **/

class CartPage extends Page{

	public static $db = array();

	public static $has_one = array(
		'CheckoutPage' => 'CheckoutPage',
		'ContinuePage' => 'SiteTree'
	);

	public static $icon = 'ecommerce/images/icons/cart';

	/**
	 *@return Fieldset
	 **/
	function getCMSFields(){
		$fields = parent::getCMSFields();
		if($checkouts = DataObject::get('CheckoutPage')) {
			$fields->addFieldToTab('Root.Content.Links',new DropdownField('CheckoutPageID','Checkout Page',$checkouts->toDropdownMap()));
		}
		$fields->addFieldToTab('Root.Content.Links',new TreeDropdownField('ContinuePageID','Continue Page',"SiteTree"));
		return $fields;
	}

	/**
	 *@return String (HTML Snipper)
	 **/
	function EcommerceMenuTitle() {
		$count = 0;
		$order = ShoppingCart::current_order();
		if($order) {
			$count = $order->TotalItems();
		}
		$v = $this->MenuTitle;
		if($count) {
			$v .= " <span class=\"numberOfItemsInCart\">(".$count.")</span>";
		}
		return $v;
	}

	/**
	 * Returns the link or the Link to the account page on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if(!$page = DataObject::get_one('CartPage')) {
			return CheckoutPage::link();
		}
		return $page->Link();
	}

	/**
	 * Return a link to view the order on this page.
	 * @return String (URLSegment)
	 * @param int|string $orderID ID of the order
	 */
	public static function get_order_link($orderID) {
		return self::find_link(). 'showorder/' . $orderID . '/';
	}

}

class CartPage_Controller extends Page_Controller{

	protected $currentOrder = null;

	protected $orderID = 0;

	protected $memberID = 0;

	public function init() {
		parent::init();
		//ShoppingCart::add_requirements();
		Requirements::themedCSS('CheckoutPage');
		$orderID = intval($this->getRequest()->param('ID'));
		//WE HAVE THIS FOR SUBMITTING FORMS!
		if(isset($_POST['OrderID'])) {
			$this->orderID = intval($_POST['OrderID']);
		}
	}

	/**
	 *@return DataObject(Order) or NULL
	 **/
	function CurrentOrder() {
		if(!$this->currentOrder) {
			if($this->orderID) {
				$this->currentOrder = Order::get_by_id_if_can_view($this->orderID);
			}
			else {
				$this->currentOrder = ShoppingCart::current_order();
			}
		}
		return $this->currentOrder;
	}

	/**
	 *@return array just so that template shows -  sets CurrentOrder variable
	 **/
	function showorder($request) {
		Requirements::themedCSS('Order');
		Requirements::themedCSS('Order_print', 'print');
		$this->orderID = intval($request->param("ID"));
		if(!$this->CurrentOrder()) {
			$this->message = _t('CartPage.ORDERNOTFOUND', 'Order can not be found.');
		}
		return array();
	}

}




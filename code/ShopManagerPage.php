<?php
/**
* @author Nicolaas [at] sunnysideup.co.nz
* @package: ecommerce
* @description: this is an extra page which allows you to manage your shop
*/


class ShopManagerPage extends Page {

	public static $icon = "ecommerce/images/treeicons/shopmanager";

	public static $defaults = array(
		"ShowInMenus" => 0,
		"ShowInSearch" => 0
	);

	function canCreate($member = null) {
		return !DataObject::get_one("SiteTree", "\"ClassName\" = 'ShopManagerPage'");
	}

	/**
	 *
	 *@return Boolean
	 **/
	function canView($member = null) {
		if(Permission::check("SHOP_ADMIN")) {
			return true;
		}
		else {
			//Security::permissionFailure($this, _t('ShopManagerPage.PERMFAILURE',' This page is secured and you need (shop) administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}


}

class ShopManagerPage_Controller extends Page_Controller {

	function init() {
		// Only administrators can run this method
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::themedCSS("ShopManagerPage");
		Requirements::javascript("ecommerce/javascript/ShopManagerPage.js");
	}

	/**
	 *
	 *@return CheckoutPage Object
	 **/
	function CheckoutPage() {
		return DataObject::get_one("CheckoutPage");
	}

	/**
	 *
	 *@return AccountPage Object
	 **/
	function AccountPage() {
		return DataObject::get_one("AccountPage");
	}

	/**
	 *
	 *@return DataObjectSet
	 **/
	function LastOrders() {
		return DataObject::get("Order", "", "\"Created\" DESC", "", "0, 250");
	}

	function clearcompletecart() {
		ShoppingCart::clear();
		if($m = Member::currentUser()) {
			$m->logout();
		}
		for($i = 0; $i < 5; $i++) {
			$_SESSION = array();
			unset($_SESSION);
			ShoppingCart::clear();
			$_SESSION = array();
			unset($_SESSION);
		}
		die('<a href="goback">Shopping cart has been removed, click here to continue ...</a>');
	}

	function goback() {
		Director::redirectBack();
		return false;
	}

	function getorderdetailsforadmin() {
		$orderID = intval(Director::URLParam("ID"));
		$dos = DataObject::get("OrderModifier", "\"OrderID\" = '$orderID'");
		$v = print_r($dos);
		$this->Content = $v;
		return array();
	}

	function testorderreceipt() {
		$orderID = intval(Director::URLParam("ID"));
		if(!$orderID) {
			$o = DataObject::get_one("Order", "", "\"Created\" DESC");
			if($o) {
				$orderID = $o->ID;
			}
		}
		if($orderID) {
			$order = Order::get_by_id_if_can_view($orderID);
			if($order) {
				$from = $order->getReceiptEmail();
				$to = $order->Member()->Email;
				$subject = $order->getReceiptSubject();
				$purchaseCompleteMessage = DataObject::get_one('CheckoutPage')->PurchaseComplete;

				$data = array(
					'PurchaseCompleteMessage' => $purchaseCompleteMessage,
					'Order' => $order,
					'From' => $from,
					'To' => $to,
					'Subject' => $subject
				);
				Requirements::clear();
				return $this->customise($data)->renderWith("Order_ReceiptEmail");
			}
		}
		else {
			$this->Content = "<h1>NO ORDER FOUND!</h1>";
		}
		return array();
	}


	function teststatusupdatemail() {
		$orderID = intval(Director::URLParam("ID"));
		if(!$orderID) {
			$o = DataObject::get_one("Order", "", "\"Created\" DESC");
			if($o) {
				$orderID = $o->ID;
			}
		}
		if($orderID) {
			$order = Order::get_by_id_if_can_view($orderID);
			if($order) {
				$from = Order::get_receipt_email();
				$to = $order->Member()->Email;
				$subject = "Your order status";
				$logs = DataObject::get('OrderStatusLog', "\"OrderID\" = {$this->ID}", "\"Created\" DESC", null, 1);
				if($logs) {
					$latestLog = $logs->First();
					$note = $latestLog->Note;
				}
				else {
					$note = "Note goes here";
				}
				$member = $order->Member();
				$data = array(
					'Order' => $order,
					'From' => $from,
					'To' => $to,
					"Member" => $member,
					"Note" => $note
				);
				Requirements::clear();
				return $this->customise($data)->renderWith("Order_StatusEmail");
			}
		}
		else {
			$this->Content = "<h1>NO ORDER FOUND!</h1>";
		}
		return array();
	}

	function showorder($request) {
		Requirements::themedCSS('Order');
		Requirements::themedCSS('Order_print', 'print');
		$accountPageLink = AccountPage::find_link();
		if($orderID = $request->param('ID')) {
			if($order = Order::get_by_id_if_can_view($orderID)) {
				return array('Order' => $order);
			}
			else {
				return array(
					'Order' => false,
					'Message' => 'There is no order by that ID. '
				);
			}
		}
	}

	function currentorderdetails() {
		$currentOrder = ShoppingCart::current_order();
		$html = '';

		if($items = $currentOrder->Items()) {
			foreach($items as $item) $html .= $item->debug();
		}

		if($modifiers = $currentOrder->Modifiers()) {
			foreach($modifiers as $modifier) $html .= $modifier->debug();
		}
		return array(
			'Message' => $html,
			'Content' => $html
		);

	}

}

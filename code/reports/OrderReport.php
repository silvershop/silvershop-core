<?php
/**
 * This is a stand-alone controller, designed to be
 * used with the eCommerce reporting system.
 *
 * It allows a user to view a template for a packing
 * slip of an order, or an invoice with status logs.
 *
 * @see CurrentOrdersReport
 * @see UnprintedOrderReport
 *
 * @package ecommerce
 */
class OrderReport_Popup extends Controller {
	
	//basic security for controller
	static $allowed_actions = array(
		'index' => 'ADMIN',
		'packingslip' => 'ADMIN',
		'invoice' => 'ADMIN'
	);

	function init(){
		parent::init();
		//include print javascript, if print argument is provided
		if(isset($_REQUEST['print']) && $_REQUEST['print']) {
			Requirements::customScript("if(document.location.href.indexOf('print=1') > 0) {window.print();}");
		}
		$this->Title = i18n::_t("ORDER.INVOICE","Invoice");
		if($id = $this->urlParams['ID']) {
			$this->Title .= " #$id";
		}
		/*Requirements::themedCSS("reset");*/
		/*Requirements::themedCSS("OrderReport");*/
		/*Requirements::themedCSS("OrderReport_Print", "print");*/
	}

	/**
	 * This is the default action of this
	 * controller without calling any
	 * explicit action, such as "show".
	 *
	 * This default "action" will show
	 * order information in a printable view.
	 */
	function index() {
		return $this->renderWith('Order_Printable');
	}


	function Link($action = null) {
		return "OrderReport_Popup/$action";
	}

	/**
	 * This method is used primarily for cheque orders.
	 *
	 * @TODO Why is this specific to cheque?
	 *
	 * @return unknown
	 */
	function SingleOrder(){
		$id = $this->urlParams['ID'];

		if(is_numeric($id)) {
			$order = DataObject::get_by_id('Order', $id);
			$payment = $order->Payment();
			$cheque = false;

			if($payment->First()) {
				$record = $payment->First();
				if($record->ClassName == 'ChequePayment') {
					$cheque = true;
				}
			}

			return new ArrayData(array(
				'DisplayFinalisedOrder' => $order,
				'IsCheque' => $cheque
			));
		}

		return false;
	}

	/**
	 * @TODO Get orders by ID or using current filter if ID is not numeric (for getting all orders)
	 * @TODO Define what the role of this method is. Is it for templates, is it for a report?
	 *
	 * @return unknown
	 */
	function DisplayFinalisedOrder() {
		$id = $this->urlParams['ID'];

		if(is_numeric($id)) {
			$order = DataObject::get_by_id("Order", $id);
			if(isset($_REQUEST['print'])) {
				$order->updatePrinted(true);
			}

			return $order;
		}

		return false;
	}
	
	function SiteConfig() {
		return SiteConfig::current_site_config();
	}
}

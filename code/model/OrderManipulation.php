<?php

/**
 * Provides forms and processing to a controller for editing an order that has been previously placed.
 * @package shop
 * @subpackage forms
 */
class OrderManipulation extends Extension{

	static $allow_cancelling = false;
	static $allow_paying = false;

	static $session_variable = "OrderManipulation.historicalorders";
	
	static function set_allow_cancelling($cancel = true){self::$allow_cancelling = $cancel;}
	static function set_allow_paying($pay = true){self::$allow_paying = $pay;}

	static $allowed_actions = array(
		'CancelForm',
		'PaymentForm'
	);

	/**
	 * Add an order to the session-stored history of orders.
	 */
	static function add_session_order(Order $order){
		$history = self::get_session_order_ids();
		if(!is_array($history)){
			$history = array();
		}
		$history[$order->ID] = $order->ID;
		Session::set(self::$session_variable,$history);
	}
	
	/**
	 * Get historical orders for current session.
	 */
	static function get_session_order_ids(){
		$history = Session::get(self::$session_variable);
		if(!is_array($history)){
			$history = null;
		}
		return $history;
	}
	
	/**
	 * Get the order via url 'ID' or form submission 'OrderID'.
	 * It will check for permission based on session stored ids or member id.
	 *
	 * @return the order
	 */
	public function orderfromid($extrafilter = null){
		$orderid = Director::urlParam('ID');
		if(!$orderid){
			$orderid = (isset($_POST['OrderID'])) ? $_POST['OrderID'] : null;
		}
		if(!is_numeric($orderid)){
			return null;
		}
		$order = null;
		$filter = $this->orderfilter();
		if($extrafilter){
			$filter .= " AND $extrafilter";
		}
		$idfilter = ($orderid) ? " AND \"ID\" = $orderid" : "";
		return DataObject::get_one('Order',$filter.$idfilter,true,"Created DESC");
	}

	/**
	 * Get all orders for current member / session.
	 * @return DataObjectSet of Orders
	 */
	public function allorders($filter = "",$orderby = ""){
		if($filter && $filter != "") $filter = " AND ".$filter;
		return DataObject::get('Order',$this->orderfilter().$filter,$orderby);
	}

	/**
	 * Makes sure to only get carts relating to session, or member id
	 */
	protected function orderfilter(){
		$memberid = Member::currentUserID();
		//session orders
		$sessids = self::get_session_order_ids();
		$ids = is_array($sessids) ? implode(',',$sessids) : "-1";
		$filter = "\"ID\" IN ($ids)";
		$filter =  ($memberid) ? "($filter OR \"MemberID\" = $memberid)" : $filter;
		return $filter;
	}

	/**
	 * Return all past orders for current member / session.
	 */
	function PastOrders($extrafilter = null){
		$statusFilter = "\"Order\".\"Status\" IN ('" . implode("','", Order::$placed_status) . "')";
		$statusFilter .= " AND \"Order\".\"Status\" NOT IN('". implode("','", Order::$hidden_status) ."')";
		$statusFilter .= ($extrafilter) ? " AND $extrafilter" : "";
		return $this->allorders($statusFilter);
	}

	/**
	 * Returns the form to cancel the current order,
	 * checking to see if they can cancel their order
	 * first of all.
	 *
	 * @return Order_CancelForm
	 */
	function CancelForm() {
		if(self::$allow_cancelling && $order = $this->orderfromid()) {
			if($order->canCancel()) {
				$form = new CancelOrderForm($this->owner, 'CancelForm', $order->ID);
				$form->extend('updateCancelForm',$order);
				return $form;
			}
		}
		return null;
	}

	/**
	 * Creates form to pay for incomplete orders
	 *@return Form (OrderForm_Payment) or Null
	 **/
	function PaymentForm(){
		//TODO: handle pending payments better: eg if a cheque payment has been made, there's no point allowing another.
		if(self::$allow_paying && $order = $this->orderfromid()){
			Requirements::javascript(SHOP_DIR."/javascript/EcomPayment.js");
			if($order->canPay()){
				$form = new OutstandingPaymentForm($this->owner, 'PaymentForm', $order);
				$form->extend('updatePaymentForm',$order);
				return $form;
			}
		}
		return null;
	}

	protected $sessionmessage = null;
	protected $sessionmessagetype = null;

	function setSessionMessage($message = "success",$type = "good"){
		Session::set('OrderManipulation.Message',$message);
		Session::set('OrderManipulation.MessageType',$type);
	}

	function SessionMessage(){
		if($message = Session::get("OrderManipulation.Message")){
			$this->sessionmessage = $message;
			Session::clear("OrderManipulation.Message");
		}
		return $this->sessionmessage;
	}

	function SessionMessageType(){
		if($type = Session::get("OrderManipulation.MessageType")){
			$this->sessionmessagetype = $type;
			Session::clear("OrderManipulation.MessageType");
		}
		return $this->sessionmessagetype;
	}

}
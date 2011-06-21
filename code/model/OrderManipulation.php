<?php

/**
 * Provides forms and processing to a controller for editing an order that has been previously placed.
 */

class OrderManipulation extends Extension{
	
	static $allow_cancelling = false;
	static $allow_paying = false;
	
	static function set_allow_cancelling($cancel = true){self::$allow_cancelling = $cancel;}
	static function set_allow_paying($pay = true){self::$allow_paying = $pay;}

	static $allowed_actions = array(
		'CancelForm',
		'PaymentForm'
	);
	
	/**
	 * Get the order via url 'ID' or form submission 'OrderID'.
	 * It will check for permission based on session id or member id.
	 * 
	 * @return the order
	 */
	public function orderfromid($extrafilter = null){
		$orderid = Director::urlParam('ID');
		if(!$orderid) $orderid = (isset($_POST['OrderID']) && is_numeric($_POST['OrderID'])) ? $_POST['OrderID'] : null;
		$order = null;
		$filter = $this->orderfilter();
		if($extrafilter) $filter .= " AND $extrafilter";
		$idfilter = ($orderid) ? " AND \"ID\" = $orderid" : "";
		//security filter to only allow viewing orders associated with this session, or member id
		$order = DataObject::get_one('Order',"\"Status\" NOT IN('Cart','AdminCancelled','MemberCancelled') AND ".$filter.$idfilter,true,"Created DESC");
		//if no id, then get first of latest orders for member or session id?
		/*
		 //TODO: permission message on failure
		if(!$order){
			//order doesn't exist, or don't have permission
			$this->setSessionMessage($reason,'bad');
		}
		*/
		return $order;
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
		$sessionid = session_id();
		$filter = "\"SessionID\" = '$sessionid'";
		$filter =  ($memberid) ? "($filter OR \"MemberID\" = $memberid)" : $filter;
		return $filter;
	}
	
	/**
	 * Return all past orders for current member / session.
	 */
	function PastOrders(){
		return $this->allorders("\"Status\" IN('Paid','Complete','Sent','Unpaid','Processing')");
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
				$form = new Order_CancelForm($this->owner, 'CancelForm', $order->ID);
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
			Requirements::javascript(ECOMMERCE_DIR."/javascript/EcomPayment.js");
			if($order->canPay()){
				$form = new Order_PaymentForm($this->owner, 'PaymentForm', $order);
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
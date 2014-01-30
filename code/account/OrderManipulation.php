<?php

/**
 * Provides forms and processing to a controller for editing an order that has been previously placed.
 * @package shop
 * @subpackage forms
 */
class OrderManipulation extends Extension{

	private static $allowed_actions = array(
		'ActionsForm',
		'order'
	);

	private static $sessname = "OrderManipulation.historicalorders";

	/**
	 * Add an order to the session-stored history of orders.
	 */
	public static function add_session_order(Order $order){
		$history = self::get_session_order_ids();
		if(!is_array($history)){
			$history = array();
		}
		$history[$order->ID] = $order->ID;
		Session::set(self::$sessname,$history);
	}
	
	/**
	 * Get historical orders for current session.
	 */
	public static function get_session_order_ids(){
		$history = Session::get(self::$sessname);
		if(!is_array($history)){
			$history = null;
		}
		return $history;
	}
	
	public static function clear_session_order_ids(){
		Session::set(self::$sessname,null);
		Session::clear(self::$sessname);
	}
	
	/**
	 * Get the order via url 'ID' or form submission 'OrderID'.
	 * It will check for permission based on session stored ids or member id.
	 *
	 * @return the order
	 */
	public function orderfromid($extrafilter = null){
		$orderid = $this->owner->getRequest()->param('ID');
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
		$sessids = self::get_session_order_ids();
		$ids = is_array($sessids) ? implode(',',$sessids) : "-1";
		$filter = "\"ID\" IN ($ids)";
		$filter =  ($memberid) ? "($filter OR \"MemberID\" = $memberid)" : $filter;
		return $filter;
	}

	/**
	 * Return all past orders for current member / session.
	 */
	public function PastOrders($extrafilter = null){
		$statusFilter = "\"Order\".\"Status\" IN ('" . implode("','", Order::$placed_status) . "')";
		$statusFilter .= " AND \"Order\".\"Status\" NOT IN('". implode("','", Order::$hidden_status) ."')";
		$statusFilter .= ($extrafilter) ? " AND $extrafilter" : "";
		return $this->allorders($statusFilter);
	}
	
	/**
	 * Return the {@link Order} details for the current
	 * Order ID that we're viewing (ID parameter in URL).
	 *
	 * @return array of template variables
	 */
	public function order(SS_HTTPRequest $request) {	
		$message = null;
		$order = $this->orderfromid();
		if(!$order) {
			return $this->owner->httpError(404,"Order could not be found");
		}
		return array(
			'Order' => $order,
			'Form' => $this->ActionsForm() //see OrderManipulation extension
		);
	}
	
	/**
	 * Build a form for cancelling, or retrying payment for a placed order.
	 * @return Form
	 */
	public function ActionsForm(){
		if($order = $this->orderfromid()){
			$form = new OrderActionsForm($this->owner, "ActionsForm",$order);
			$form->extend('updateActionsForm',$order);
			return $form;
		}
		return null;
	}

	protected $sessionmessage, $sessionmessagetype = null;

	public function setSessionMessage($message = "success",$type = "good"){
		Session::set('OrderManipulation.Message',$message);
		Session::set('OrderManipulation.MessageType',$type);
	}

	public function SessionMessage(){
		if($message = Session::get("OrderManipulation.Message")){
			$this->sessionmessage = $message;
			Session::clear("OrderManipulation.Message");
		}
		return $this->sessionmessage;
	}

	public function SessionMessageType(){
		if($type = Session::get("OrderManipulation.MessageType")){
			$this->sessionmessagetype = $type;
			Session::clear("OrderManipulation.MessageType");
		}
		return $this->sessionmessagetype;
	}
	
}
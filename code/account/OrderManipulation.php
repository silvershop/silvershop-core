<?php
/**
 * Provides forms and processing to a controller for editing an
 * order that has been previously placed.
 *
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
	public static function add_session_order(Order $order) {
		$history = self::get_session_order_ids();
		if(!is_array($history)){
			$history = array();
		}
		$history[$order->ID] = $order->ID;
		Session::set(self::$sessname, $history);
	}

	/**
	 * Get historical orders for current session.
	 */
	public static function get_session_order_ids() {
		$history = Session::get(self::$sessname);
		if(!is_array($history)){
			$history = null;
		}
		return $history;
	}

	public static function clear_session_order_ids() {
		Session::set(self::$sessname, null);
		Session::clear(self::$sessname);
	}

	/**
	 * Get the order via url 'ID' or form submission 'OrderID'.
	 * It will check for permission based on session stored ids or member id.
	 *
	 * @return the order
	 */
	public function orderfromid() {
		$request = $this->owner->getRequest();
		$id = (int)$request->param('ID');
		if(!$id){
			$id = (int)$request->postVar('OrderID');
		}

		return $this->allorders()->byID($id);
	}

	/**
	 * Get all orders for current member / session.
	 * @return DataObjectSet of Orders
	 */
	public function allorders() {
		$filters = array(
			'ID' => -1 //ensures no results are returned
		);
		if($sessids = self::get_session_order_ids()){
			$filters['ID'] = $sessids;
		}
		if($memberid = Member::currentUserID()){
			$filters['MemberID'] = $memberid;
		}

		return Order::get()->filterAny($filters)
				->filter("Status:not", Order::config()->hidden_status);
	}

	/**
	 * Return all past orders for current member / session.
	 */
	public function PastOrders() {
		return $this->allorders()
				->filter("Status", Order::config()->placed_status);
	}

	/**
	 * Return the {@link Order} details for the current
	 * Order ID that we're viewing (ID parameter in URL).
	 *
	 * @return array of template variables
	 */
	public function order(SS_HTTPRequest $request) {
		//move the shopping cart session id to past order ids, if it is now an order
		ShoppingCart::singleton()->archiveorderid();
		$order = $this->orderfromid();
		if(!$order) {
			return $this->owner->httpError(404, "Order could not be found");
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
	public function ActionsForm() {
		if($order = $this->orderfromid()){
			$form = new OrderActionsForm($this->owner, "ActionsForm", $order);
			$form->extend('updateActionsForm', $order);

			return $form;
		}
	}

	protected $sessionmessage;
	protected $sessionmessagetype = null;

	public function setSessionMessage($message = "success",$type = "good") {
		Session::set('OrderManipulation.Message', $message);
		Session::set('OrderManipulation.MessageType', $type);
	}

	public function SessionMessage() {
		if($message = Session::get("OrderManipulation.Message")){
			$this->sessionmessage = $message;
			Session::clear("OrderManipulation.Message");
		}

		return $this->sessionmessage;
	}

	public function SessionMessageType() {
		if($type = Session::get("OrderManipulation.MessageType")){
			$this->sessionmessagetype = $type;
			Session::clear("OrderManipulation.MessageType");
		}

		return $this->sessionmessagetype;
	}

}

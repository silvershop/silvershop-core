<?php
/**
 * Handles tasks to be performed on orders, particularly placing and processing/fulfilment.
 * Placing, Emailing Reciepts, Status Updates, Printing, Payments - things you do with a completed order.
 *
 * @package shop
 */
class OrderProcessor{

	/**
	 * @var Order
	 */
	protected $order;

	/**
	 * @var OrderEmailNotifier
	 */
	protected $notifier;

	/**
	 * @var string
	 */
	protected $error;

	/**
	 * Static way to create the order processor.
	 * Makes creating a processor easier.
	 * @param Order $order
	 */
	public static function create(Order $order) {
		return Injector::inst()->create('OrderProcessor', $order);
	}
	/**
	 * Assign the order to a local variable
	 * @param Order $order
	 */
	public function __construct(Order $order) {
		$this->order = $order;
		$this->notifier = OrderEmailNotifier::create($order);
	}

	/**
	 * URL to display success message to the user. 
	 * Happens after any potential offsite gateway redirects.
	 * 
	 * @return String Relative URL
	 */
	public function getReturnUrl() {
		return $this->order->Link();
	}

	/**
	 * Create a payment model, and provide link to redirect to external gateway,
	 * or redirect to order link.
	 * @return string - url for redirection after payment has been made
	 */
	public function makePayment($gateway, $gatewaydata = array()) {
		//create payment
		$payment = $this->createPayment($gateway);
		if(!$payment){
			//errors have been stored.
			return false;
		}

		// Create a purchase service, and set the user-facing success URL for redirects
		$service = PurchaseService::create($payment)
					->setReturnUrl($this->getReturnUrl());

		// Process payment, get the result back
		$response = $service->purchase($this->getGatewayData($gatewaydata));
		if(GatewayInfo::is_manual($gateway)){
			//don't complete the payment at this stage, if payment is manual
			$this->placeOrder();
		}elseif($response->isSuccessful()) {
			$this->completePayment();
		}
		return $response;
	}

	/**
	 * Map shop data to omnipay fields
	 * 
	 * @param array $customData Usually user submitted data.
	 * @return array
	 */
	protected function getGatewayData($customData) {
		$shipping = $this->order->getShippingAddress();
		$billing = $this->order->getBillingAddress();
		
		return array_merge(
			$customData,
			array(
				'transactionId' => $this->order->Reference,
				'firstName' => $this->order->FirstName,
				'lastName' => $this->order->Surname,
				'email' => $this->order->Email,
				'company' => $this->order->Company,
				'billingAddress1' => $billing->Address,
				'billingAddress2' => $billing->AddressLine2,
				'billingCity' => $billing->City,
				'billingPostcode' => $billing->PostalCode,
				'billingState' => $billing->State,
				'billingCountry' => $billing->Country,
				'billingPhone' => $billing->Phone,
				'shippingAddress1' => $shipping->Address,
				'shippingAddress2' => $shipping->AddressLine2,
				'shippingCity' => $shipping->City,
				'shippingPostcode' => $shipping->PostalCode,
				'shippingState' => $shipping->State,
				'shippingCountry' => $shipping->Country,
				'shippingPhone' => $shipping->Phone,
			)
		);
	}

	/**
	 * Create a new payment for an order
	 */
	public function createPayment($gateway) {
		if(!GatewayInfo::is_supported($gateway)) {
			$this->error(_t("PaymentProcessor.INVALIDGATEWAY", "`$gateway` isn't a valid payment gateway."));
			return false;
		}
		if(!$this->order->canPay(Member::currentUser())){
			$this->error(_t("PaymentProcessor.CANTPAY", "Order can't be paid for."));
			return false;
		}
		$payment = Payment::create()
			->init($gateway, $this->order->TotalOutstanding(), ShopConfig::get_base_currency());
		$this->order->Payments()->add($payment);
		return $payment;
	}

	/**
	 * Complete payment processing
	 *    - send receipt
	 * 	- update order status accordingling
	 * 	- fire event hooks
	 */
	public function completePayment() {
		if(!$this->order->Paid){
			$this->order->extend('onPayment'); //a payment has been made
			//place the order, if not already placed
			if($this->canPlace($this->order)){
				$this->placeOrder();
			}

			if(
				// Standard order
				($this->order->GrandTotal() > 0 && $this->order->TotalOutstanding() <= 0)
				// Zero-dollar order (e.g. paid with loyalty points)
				|| ($this->order->GrandTotal() == 0 && Order::config()->allow_zero_order_total)
			){
				//set order as paid
				$this->order->Status = 'Paid';
				$this->order->Paid = SS_Datetime::now()->Rfc2822();
				$this->order->write();
				foreach($this->order->Items() as $item){
					$item->onPayment();
				}
				//all payment is settled
				$this->order->extend('onPaid');
			}
			if(!$this->order->ReceiptSent){
				$this->notifier->sendReceipt();
			}
		}
	}

	/**
	 * Determine if an order can be placed.
	 * @param boolean $order
	 */
	public function canPlace(Order $order) {
		if(!$order){
			$this->error(_t("OrderProcessor.NULL", "Order does not exist."));
			return false;
		}
		//order status is applicable
		if(!$order->IsCart()){
			$this->error(_t("OrderProcessor.NOTCART", "Order is not a cart."));
			return false;
		}
		//order has products
		if($order->Items()->Count() <= 0){
			$this->error(_t("OrderProcessor.NOITEMS", "Order has no items."));
			return false;
		}
		
		return true;
	}

	/**
	 * Takes an order from being a cart to awaiting payment.
	 * @param Member $member - assign a member to the order
	 * @return boolean - success/failure
	 */
	public function placeOrder() {
		if(!$this->order){
			$this->error(_t("OrderProcessor.NULL", "A new order has not yet been started."));
			return false;
		}
		if(!$this->canPlace($this->order)){ //final cart validation
			return false;
		}
		//remove from session
		$cart = ShoppingCart::curr();
		if($cart && $cart->ID == $this->order->ID){
			ShoppingCart::singleton()->clear();
		}
		//update status
		if($this->order->TotalOutstanding()){
			$this->order->Status = 'Unpaid';
		}else{
			$this->order->Status = 'Paid';
		}
		if(!$this->order->Placed){
			$this->order->Placed = SS_Datetime::now()->Rfc2822(); //record placed order datetime
			if($request = Controller::curr()->getRequest()){
				$this->order->IPAddress = $request->getIP(); //record client IP
			}
		}
		//re-write all attributes and modifiers to make sure they are up-to-date before they can't be changed again
		$items = $this->order->Items();
		if($items->exists()){
			foreach($items as $item){
				$item->onPlacement();
				$item->write();
			}
		}
		$modifiers = $this->order->Modifiers();
		if($modifiers->exists()){
			foreach($modifiers as $modifier){
				$modifier->write();
			}
		}
		//add member to order & customers group
		if($member = Member::currentUser()){
			if(!$this->order->MemberID){
				$this->order->MemberID = $member->ID;
			}
			$cgroup = ShopConfig::current()->CustomerGroup();
			if($cgroup->exists()){
				$member->Groups()->add($cgroup);
			}
		}
		//allow decorators to do stuff when order is saved.
		$this->order->extend('onPlaceOrder');
		$this->order->write();

		//send confirmation if configured and receipt hasn't been sent
		if(
			self::config()->send_confirmation &&
				!$this->order->ReceiptSent
		) {
			$this->notifier->sendConfirmation();
		}

		//notify admin, if configured
		if(self::config()->send_admin_notification) {
			$this->notifier->sendAdminNotification();
		}

		// Save order reference to session
		OrderManipulation::add_session_order($this->order);

		return true; //report success
	}

	/**
	 * @return Order
	 */
	public function getOrder() {
		return $this->order;
	}

	public function getError() {
		return $this->error;
	}

	private function error($message) {
		$this->error = $message;
	}

	public static function config() {
		return new Config_ForClass("OrderProcessor");
	}

	/**
	* @deprecated 2.0
	*/
	public function sendEmail($template, $subject, $copyToAdmin = true) {
		Deprecation::notice('2.0', 'Use OrderEmailNotifier instead');
		return $this->notifier->sendEmail($template, $subject, $copyToAdmin);
	}

	/**
	 * @deprecated 2.0
	 */
	public function sendConfirmation() {
		Deprecation::notice('2.0', 'Use OrderEmailNotifier instead');
		$this->notifier->sendConfirmation();
	}

	/**
	* @deprecated 2.0
	*/
	public function sendReceipt() {
		Deprecation::notice('2.0', 'Use OrderEmailNotifier instead');
		$this->notifier->sendReceipt();
	}

	/**
	* @deprecated 2.0
	*/
	public function sendStatusChange($title, $note = null) {
		Deprecation::notice('2.0', 'Use OrderEmailNotifier instead');
		$this->notifier->sendStatusChange($title, $note);
	}

}

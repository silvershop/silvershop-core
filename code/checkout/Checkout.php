<?php
/**
 * Helper class for getting an order throught the checkout process
 */
class Checkout{

	/**
	 * 4 different membership schemes:
	 * 	1: creation disabled & membership not required
	 *		no body can, or is required to, become a member at checkout.
	 *	2: creation disabled & membership required
	 *		only existing members can use checkout.
	 *		(ideally the entire shop should be disabled in this case)
	 *	3: creation enabled & membership required
	 *		everyone must be, or become a member at checkout.
	 *	4: creation enabled & membership not required (default)
	 *		it is optional to be, or become a member at checkout.
	 */

	public static function member_creation_enabled() {
		return CheckoutConfig::config()->member_creation_enabled;
	}

	public static function membership_required() {
		return CheckoutConfig::config()->membership_required;
	}

	public static function get($order = null) {
		if($order === null){
			$order = ShoppingCart::curr(); //roll back to current cart
		}
		if($order->exists() && $order->isInDB()){//check if order can go through checkout
			return new Checkout($order);
		}
		return false;
	}

	protected $order;
	protected $message;
	protected $type;

	public function __construct(Order $order) {
		$this->order = $order;
	}

	/**
	 * Get stored message
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Get type of stored message
	 * @return string
	 */
	public function getMessageType() {
		return $this->type;
	}

	/**
	 * contact information
	 */
	public function setContactDetails($email, $firstname, $surname) {
		$this->order->Email = $email;
		$this->order->FirstName = $firstname;
		$this->order->Surname = $surname;
		$this->order->write();
	}

	//save / set up addresses
	public function setShippingAddress(Address $address) {
		$this->order->ShippingAddressID = $address->ID;
		if(Member::currentUserID()){
			$this->order->MemberID = Member::currentUserID();	
		} 
		$this->order->write();
		$this->order->extend('onSetShippingAddress', $address);
		//update zones and userinfo
		ShopUserInfo::singleton()->setAddress($address);
		Zone::cache_zone_ids($address);
	}

	public function setBillingAddress(Address $address) {
		$this->order->BillingAddressID = $address->ID;
		if(Member::currentUserID()){
			$this->order->MemberID = Member::currentUserID();
		}
		$this->order->write();
		$this->order->extend('onSetBillingAddress', $address);
	}

	/*
	 * Get a dataobject of payment methods.
	 */
	public function getPaymentMethods() {
		return GatewayInfo::get_supported_gateways();
	}

	/**
	 * Set payment method
	 */
	public function setPaymentMethod($paymentmethod) {
		$methods = $this->getPaymentMethods();
		if(!isset($methods[$paymentmethod])){
			Session::set("Checkout.PaymentMethod", null);
			Session::clear("Checkout.PaymentMethod");
			return $this->error(_t("Checkout.NOPAYMENTMETHOD", "Payment method does not exist"));
		}
		Session::set("Checkout.PaymentMethod", $paymentmethod);
		return true;
	}

	/**
	 * Gets the selected payment method from the session,
	 * or the only available method, if there is only one.
	 */
	public function getSelectedPaymentMethod($nice = false) {
		$methods = $this->getPaymentMethods();
		reset($methods);
		$method = count($methods) === 1 ? key($methods) : Session::get("Checkout.PaymentMethod");
		if($nice){
			$method = $methods[$method];
		}
		return $method;
	}

	/**
	 * @deprecated 1.0 use ShopMemberFactory
	 */
	public function createMembership($data) {
		$factory = new ShopMemberFactory();
		return $factory->create($data);
	}

	/**
	 * Checks if member (or not) is allowed, in accordance with configuration
	 */
	public function validateMember($member) {
		if(!CheckoutConfig::config()->membership_required){
			return true;
		}
		if(empty($member) || !($member instanceof Member)){
			return false;
		}

		return true;
	}

	/**
	 * Store a new error & return false;
	 */
	protected function error($message) {
		$this->message($message, "bad");
		return false;
	}

	/**
	 * Store a message to be fed back to user.
	 * @param string $message
	 * @param string $type - good, bad, warning
	 */
	protected function message($message, $type = "good") {
		$this->message = $message;
		$this->type = $type;
	}

}

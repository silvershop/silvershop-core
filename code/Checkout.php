<?php
/**
 * Helper class for getting an order throught the checkout process
 */
class Checkout{
	
	//4 different membership schemes:
		//1: creation disabled & membership not required = no body can, or is required to, becaome a member at checkout.
		//2: creation disabled & membership required = only existing members can use checkout. (ideally the entire shop should be disabled in this case)
		//3: creation enabled & membership required = everyone must be, or become a member at checkout.
		//4: creation enabled & membership not required (default) = it is optional to be, or become a member at checkout.

	static $member_creation_enabled = true;
	static function member_creation_enabled(){
		return self::$member_creation_enabled;
	}
	
	static $membership_required = false;
	static function membership_required(){
		return self::$membership_required;
	}
	
	static function get($order = null){
		if($order === null){
			$order = ShoppingCart::curr(); //roll back to current cart
		}		
		if($order->exists() && $order->isInDB()){//check if order can go through checkout
			return new Checkout($order);
		}
		return false;
	}
	
	protected $order;
	function __construct(Order $order){
		$this->order = $order;
	}
	
	protected $message, $type;
	
	/**
	 * Get stored message
	 * @return string
	 */
	public function getMessage(){
		return $this->message;
	}
	
	/**
	 * Get type of stored message
	 * @return string
	 */
	public function getMessageType(){
		return $this->type;
	}
	
	/**
	 * contact information
	 */
	function setContactDetails($email, $firstname, $surname){
		$this->order->Email = $email;
		$this->order->FirstName = $firstname;
		$this->order->Surname = $surname;
		$this->order->write();
	}
	
	//save / set up addresses
	function setShippingAddress(Address $address){
		$this->order->ShippingAddressID = $address->ID;
		$this->order->write();
		$this->order->extend('onSetShippingAddress',$address);
		//update zones and userinfo
		ShopUserInfo::set_location($address);
		Zone::cache_zone_ids($address);
	}

	function setBillingAddress(Address $address){
		$this->order->BillingAddressID = $address->ID;
		$this->order->write();
		$this->order->extend('onSetBillingAddress',$address);
	}
	
	/**
	 * Get shipping estimates
	 * @return DataObjectSet
	 */
	function getShippingEstimates(){
		$package = $this->order->createShippingPackage();
		$address = $this->order->getShippingAddress();
		$estimator = new ShippingEstimator($package,$address);
		$estimates = $estimator->getEstimates();
		return $estimates;
	}
	
	/*
	 * Set shipping method and shipping cost
	 * @param $option - shipping option to set, and calculate shipping from
	 * @return boolean sucess/failure of setting
	 */
	function setShippingMethod(ShippingMethod $option){
		$package = $this->order->createShippingPackage();
		if(!$package){
			return $this->error(_t("Checkout.NOPACKAGE","Shipping package information not available"));
		}
		$address = $this->order->getShippingAddress();
		if(!$address || !$address->exists()){
			return $this->error(_t("Checkout.NOADDRESS","No address has been set"));
		}
		$this->order->ShippingTotal = $option->calculateRate($package,$address);
		$this->order->ShippingMethodID = $option->ID;
		$this->order->write();
		return true;
	}
	
	/*
	 * Get a dataobject of payment methods.
	 */
	function getPaymentMethods(){
		return PaymentProcessor::get_supported_methods();
	}
	
	/**
	 * Set payment method
	 */
	function setPaymentMethod($paymentmethod){
		if(!ShopPayment::has_method($paymentmethod)){
			Session::clear("Checkout.PaymentMethod",null);
			Session::clear("Checkout.PaymentMethod");
			return $this->error(_t("Checkout.NOPAYMENTMETHOD","Payment method does not exist"));
		}
		Session::set("Checkout.PaymentMethod",$paymentmethod);
		return true;
	}
	
	/**
	 * Gets the sorted payment methdod from the session.
	 * 
	 */
	function getSelectedPaymentMethod($nice = true){
		$method = Session::get("Checkout.PaymentMethod");
		if($nice){
			$method = ShopPayment::method_title($method);
		}
		return $method;
	}
	
	/**
	 * Create member account from data array.
	 * Data must contain unique identifier.
	 * @param $data - map of member data
	 * @return Member|boolean - new member (not saved to db), or false if there is an error.
	 */
	function createMembership($data){
		if(!Checkout::$member_creation_enabled){
			return $this->error(_t("Checkout.MEMBERSHIPSNOTALLOWED","Creating new memberships is not allowed"));
		}
		$idfield = Member::get_unique_identifier_field();
		if(!isset($data[$idfield]) || empty( $data[$idfield])){
			return $this->error(sprintf(_t("Checkout.IDFIELDNOTFOUND","Required field not found: %s"),$idfield));
		}
		if(!isset($data['Password']) || empty( $data['Password'])){
			return $this->error(_t("Checkout.PASSWORDREQUIRED","A password is required"));
		}
		$idval = $data[$idfield];
		if(ShopMember::get_by_identifier($idval)){
			return $this->error(sprintf(_t("Checkout.MEMBEREXISTS","A member already exists with the %s %s"),$idfield,$idval));
		}
		$member = new Member(Convert::raw2sql($data));
		$validation = $member->validate();
		if(!$validation->valid()){
			return $this->error($validation->message());	//TODO need to handle i18n here?
		}
		return $member;
	}
	
	/**
	 * Checks if member (or not) is allowed, in accordance with configuration
	 */
	function validateMember($member){
		if(!self::$membership_required){
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
	protected function error($message){
		$this->message($message,"bad");
		return false;
	}
	
	/**
	 * Store a message to be fed back to user.
	 * @param string $message
	 * @param string $type - good, bad, warning
	 */
	protected function message($message,$type = "good"){
		$this->message = $message;
		$this->type = $type;
	}
	
}
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

	public static $member_creation_enabled = true;
	public static function member_creation_enabled(){
		return self::$member_creation_enabled;
	}

	public static $membership_required = false;
	public static function membership_required(){
		return self::$membership_required;
	}

	public static function get($order = null){
		if($order === null){
			$order = ShoppingCart::curr(); //roll back to current cart
		}
		if($order->exists() && $order->isInDB()){//check if order can go through checkout
			return new Checkout($order);
		}
		return false;
	}

	protected $order;
	public function __construct(Order $order){
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
	public function setContactDetails($email, $firstname, $surname){
		$this->order->Email = $email;
		$this->order->FirstName = $firstname;
		$this->order->Surname = $surname;
		$this->order->write();
	}

	//save / set up addresses
	public function setShippingAddress(Address $address){
		$this->order->ShippingAddressID = $address->ID;
		$this->order->MemberID = Member::currentUserID();
		$this->order->write();
		$this->order->extend('onSetShippingAddress',$address);
		//update zones and userinfo
		ShopUserInfo::set_location($address);
		Zone::cache_zone_ids($address);
	}

	public function setBillingAddress(Address $address){
		$this->order->BillingAddressID = $address->ID;
		$this->order->MemberID = Member::currentUserID();
		$this->order->write();
		$this->order->extend('onSetBillingAddress',$address);
	}

	/**
	 * Get shipping estimates
	 * @return DataObjectSet
	 */
	public function getShippingEstimates(){
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
	public function setShippingMethod(ShippingMethod $option){
		$package = $this->order->createShippingPackage();
		if(!$package){
			return $this->error(
				_t("Checkout.NOPACKAGE","Shipping package information not available")
			);
		}
		$address = $this->order->getShippingAddress();
		if(!$address || !$address->exists()){
			return $this->error(
				_t("Checkout.NOADDRESS","No address has been set")
			);
		}
		$this->order->ShippingTotal = $option->calculateRate($package,$address);
		$this->order->ShippingMethodID = $option->ID;
		$this->order->write();
		return true;
	}

	/*
	 * Get a dataobject of payment methods.
	 */
	public function getPaymentMethods(){
		return GatewayInfo::get_supported_gateways();
	}

	/**
	 * Set payment method
	 */
	public function setPaymentMethod($paymentmethod){
		$methods = $this->getPaymentMethods();
		if(!isset($methods[$paymentmethod])){
			Session::clear("Checkout.PaymentMethod",null);
			Session::clear("Checkout.PaymentMethod");
			return $this->error(_t("Checkout.NOPAYMENTMETHOD","Payment method does not exist"));
		}
		Session::set("Checkout.PaymentMethod",$paymentmethod);
		return true;
	}

	/**
	 * Gets the selected payment method from the session,
	 * or the only available method, if there is only one.
	 */
	public function getSelectedPaymentMethod($nice = false){
		$methods = $this->getPaymentMethods();
		reset($methods);
		$method = count($methods) === 1 ? key($methods) : Session::get("Checkout.PaymentMethod");
		if($nice){
			$method = $methods[$method];
		}
		return $method;
	}

	/**
	 * Create member account from data array.
	 * Data must contain unique identifier.
	 * @param $data - map of member data
	 * @return Member|boolean - new member (not saved to db), or false if there is an error.
	 */
	public function createMembership($data){
		$result = new ValidationResult();
		if(!Checkout::$member_creation_enabled){
			$result->error(
				_t("Checkout.MEMBERSHIPSNOTALLOWED","Creating new memberships is not allowed")
			);
			throw new ValidationException($result);
		}
		$idfield = Member::get_unique_identifier_field();
		if(!isset($data[$idfield]) || empty( $data[$idfield])){
			$result->error(
				sprintf(_t("Checkout.IDFIELDNOTFOUND","Required field not found: %s"),$idfield)
			);
			throw new ValidationException($result);
		}

		if(!isset($data['Password']) || empty($data['Password'])){
			$result->error(_t("Checkout.PASSWORDREQUIRED","A password is required"));
			throw new ValidationException($result);
		}

		$idval = $data[$idfield];
		if(ShopMember::get_by_identifier($idval)){
			$result->error(sprintf(
				_t("Checkout.MEMBEREXISTS","A member already exists with the %s %s"),
				_t("Member.".$idfield,$idfield),
				$idval
			));
			throw new ValidationException($result);
		}
		$member = new Member(Convert::raw2sql($data));
		$validation = $member->validate();
		if(!$validation->valid()){
			$result->error($validation->message());	//TODO need to handle i18n here?
		}

		if(!$result->valid()){
			throw new ValidationException($result);
		}

		return $member;
	}

	/**
	 * Checks if member (or not) is allowed, in accordance with configuration
	 */
	public function validateMember($member){
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

<?php
/**
 * Helper class for getting an order throught the checkout process
 */
class Checkout{
	
	static $user_membership_required = false;
	static function user_membership_required(){
		return self::$user_membership_required;
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
		//TODO: allow submitting array of data, which gets validated as an address?
		$this->order->ShippingAddressID = $address->ID;
		$this->order->write();
		$this->order->extend('onSetShippingAddress',$address);
	}
	
	function setBillingAddress(Address $address){
		//TODO: allow submitting array of data, which gets validated as an address?
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
	
	//set discount code
	
	//get payment methods
	function getPaymentMethods(){
		$set = new DataObjectSet();
		foreach(Payment::get_supported_methods() as $class => $name){
			$set->push(new ArrayData(array(
				'Title' => $name,
				'ClassName' => $class	
			)));	
		}		
		return $set;
	}
	
	/**
	 * Set payment method
	 */
	function setPaymentMethod($paymentmethod){
		//TODO: check if method even exists
		
		Session::set("Checkout.PaymentMethod",$paymentmethod);
		return true;
	}
	
	function getSelectedPaymentMethod(){
		return Session::get("Checkout.PaymentMethod");
	}
	
	//display final data
	
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
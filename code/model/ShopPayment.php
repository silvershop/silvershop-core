<?php
/**
 * Customisations to {@link Payment} specifically
 * for the shop module.
 *
 * @package shop
 */
class ShopPayment extends DataExtension {
	
	
	static $has_one = array(
		'Order' => 'Order' //redundant...should be using PaidObject
	);
	
	public static $summary_fields = array(
		"OrderID" => "Order ID",
		"ClassName" => "Type",
		"Amount" => "Amount",
		"Status" => "Status"
	);
	
	static function set_supported_methods($methods){
		Payment::set_supported_methods($methods);
	}
	
	static function get_supported_methods(){
		return Payment::get_supported_methods(); //Warning: this is only available on a custom version of Payment module
	}
	
	static function get_method_dataset(){
		$set = new ArrayList();
		foreach(self::get_supported_methods() as $method => $name){
			$set->push(new ArrayData(array(
				'Title' => self::method_title($method),
				'ClassName' => $method
				//TODO: introduce image, and other useful data
			)));
		}
		return $set;
	}
	
	/*
	 * Return i18n string to represent payment type.
	*/
	static function method_title($method){
		$paymentmethods = self::get_supported_methods();
		if(isset($paymentmethods[$method])){
			return _t("ShopPayment.".strtoupper($method),$paymentmethods[$method]);
		}
		return $method;
	}
	
	static function has_method($method){
		$methods = self::get_supported_methods();
		return isset($methods[$method]);
	}

	function canCreate($member = null) {
		return false;
	}

	function canDelete($member = null) {
		return false;
	}
	
	/**
	 * Update order status when payment is sucessful.
	 * This is called when payment status is updated in Payment.
	 */
	function onAfterWrite() {
		if($this->owner->Status == 'Success' && $order = $this->owner->Order()) {
			OrderProcessor::create($order)->completePayment();
		}
	}

	function redirectToOrder() {
		$order = $this->owner->Order();
		Controller::curr()->redirect($order->Link());
		return;
	}

	function setPaidObject(DataObject $do){
		$this->owner->PaidForID = $do->ID;
		$this->owner->PaidForClass = $do->ClassName;
	}

	function Status() {
   	return _t('Payment.'.$this->owner->Status,$this->owner->Status);
	}

}
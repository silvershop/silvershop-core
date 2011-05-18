<?php
/**
 * @description Customisations to {@link Payment} specifically for the ecommerce module.
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 *
 * @package: ecommerce
 * @sub-package: payment
 *
 **/
class EcommercePayment extends DataObjectDecorator {

	public static $summary_fields = array(
		"OrderID" => "Order ID",
		"ClassName" => "Type",
		"AmountValue" => "Amount",
		"Status" => "Status"
	);

	function extraStatics() {
		return array(
			'has_one' => array(
				'Order' => 'Order' //redundant...should be using PaidObject
			),
			'casting' => array(
				'AmountValue' => 'Currency'
			),
			'summary_fields' => self::$summary_fields,
			'searchable_fields' => array(
				'OrderID' => array(
					'field' => 'TextField',
					'title' => 'Order Number'
				),
				'Created' => array(
					'title' => 'Date (e.g. today)',
					'field' => 'TextField',
					//'filter' => 'PaymentFilters_AroundDateFilter', //TODO: this breaks the sales section of the CMS
				),
				'IP' => array(
					'title' => 'IP Address',
					'filter' => 'PartialMatchFilter'
				),
				'Status'
			)
		);
	}

	public static function process_payment_form_and_return_next_step($order, $form, $data, $paidBy = null) {
		if(!$order){
			user_error('Order not found', E_USER_ERROR);
			return;
		}
		if(!$paidBy) {
			$paidBy = Member::currentUser();
		}
		$paymentClass = (!empty($data['PaymentMethod'])) ? $data['PaymentMethod'] : null;
		$payment = class_exists($paymentClass) ? new $paymentClass() : null;
		if(!($payment && $payment instanceof Payment)) {
			user_error(get_class($payment) . ' is not a valid Payment object.', E_USER_ERROR);
		}
		// Save payment data from form and process payment
		$form->saveInto($payment);
		$payment->OrderID = $order->ID;
		if(is_object($paidBy)) {
			$payment->PaidByID = $paidBy->ID;
		}
		$payment->Amount = $order->TotalOutstandingAsMoneyObject();
		$payment->write();
		// Process payment, get the result back
		$result = $payment->processPayment($data, $form);
		// isProcessing(): Long payment process redirected to another website (PayPal, Worldpay)
		if($result->isProcessing()) {
			return $result->getValue();
		}
		else {			
			Director::redirect($order->Link());
			return true;		
		}
	}

	/**
	 *@return Boolean
	 **/
	function canCreate($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if($member) {
			return $member->IsShopAdmin();
		}
		return false;
	}
	/**
	 *@return Boolean
	 **/
	function canDelete($member = null) {
		return false;
	}


	function updateCMSFields(&$fields){
		//DOES NOT WORK RIGHT NOW AS supported_methods is PROTECTED
		//$options = $this->owner::$supported_methods;
		/*
		NEEDS A BIT MORE THOUGHT...
		$classes = ClassInfo::subclassesFor("Payment");
		unset($classes["Payment"]);
		if($classes && !$this->owner->ID) {
			$fields->addFieldToTab("Root.Main", new DropdownField("ClassName", "Type", $classes), "Status");
		}
		else {
			$fields->addFieldToTab("Root.Main", new ReadonlyField("ClassNameConfirmation", "Type", $this->ClassName), "Status");
		}
		*/
		$fields->replaceField("OrderID", new ReadonlyField("OrderID", "Order ID"));
		return $fields;

	}

	function redirectToOrder() {
		$order = $this->owner->Order();
		if($order) {
			Director::redirect($order->Link());
		}
		else {
			user_error("No order found with this payment: ".$this->ID, E_USER_NOTICE);
		}
		return;
	}

	function setPaidObject(DataObject $do){
		$this->owner->PaidForID = $do->ID;
		$this->owner->PaidForClass = $do->ClassName;
	}
	/**
	 *@return float
	 **/
	function AmountValue() {
		return $this->owner->Amount->getAmount();
	}

	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order ID"));
		return $fields;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		//see issue 148
		if($this->owner->OrderID) {
			$this->owner->PaidForID = $this->owner->OrderID;
			$this->owner->PaidForClass = "Order";
		}
		if($this->owner->PaidForID && !$this->owner->OrderID) {
			$this->owner->OrderID = $this->owner->PaidForID;
			$this->owner->PaidForClass = "Order";
		}
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		if($this->owner->Status == 'Success' && $order = $this->owner->Order()) {
			//NOTE: IMPORTANT
			//$order->pay($this);
		}
	}

	/**
	 *@return String
	 **/
	function Status() {
   		return _t('Payment.'.$this->owner->Status,$this->owner->Status);
	}

}

<?php
/**
 * @description Customisations to {@link Payment} specifically for the ecommerce module.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
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
				//'Created' => array('title' => 'Date','filter' => 'WithinDateRangeFilter','field' => 'DateRangeField'), //TODO: filter and field not implemented yet
				'IP' => array('title' => 'IP Address', 'filter' => 'PartialMatchFilter'),
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
		if(!$order->canPay()) {
			user_error("Order can not be paid.", E_USER_ERROR);
		}
		// Save payment data from form and process payment
		$form->saveInto($payment);
		$payment->OrderID = $order->ID;
		if($paidBy) {
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

	function canCreate($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		return $member->IsShopAdmin();
	}

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
		//TODO: throw error IF there is no OrderID
		if($this->owner->OrderID) {
			if($order = Order::get_by_id($this->owner->OrderID)) {
				$this->owner->PaidForID = $order->ID;
				$this->owner->PaidForClass = $order->ClassName;
			}
		}
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		if($this->owner->Status == 'Success' && $order = $this->owner->Order()) {
			//NOTE: IMPORTANT
			//$order->pay($this);
		}
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(isset($_GET["updatepayment"])) {
			DB::query("
				UPDATE \"Payment\"
				SET \"AmountAmount\" = \"Amount\"
				WHERE
					\"Amount\" > 0
					AND (
						\"AmountAmount\" IS NULL
						OR \"AmountAmount\" = 0
					)
			");
			$countAmountChanges = DB::affectedRows();
			if($countAmountChanges) {
				DB::alteration_message("Updated Payment.Amount field to 2.4 - $countAmountChanges rows updated", "edited");
			}
			DB::query("
				UPDATE \"Payment\"
				SET \"AmountCurrency\" = \"Currency\"
				WHERE
					\"Currency\" <> ''
					AND \"Currency\" IS NOT NULL
					AND (
						\"AmountCurrency\" IS NULL
						OR \"AmountCurrency\" = ''
					)
			");
			$countCurrencyChanges = DB::affectedRows();
			if($countCurrencyChanges) {
				DB::alteration_message("Updated Payment.Currency field to 2.4  - $countCurrencyChanges rows updated", "edited");
			}
			if($countAmountChanges != $countCurrencyChanges) {
				DB::alteration_message("Potential error in Payment fields update to 2.4, please review data", "deleted");
			}
		}
	}

	function Status() {
   	return _t('Payment.'.$this->owner->Status,$this->owner->Status);
	}




}

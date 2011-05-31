<?php
/**
 * Customisations to {@link Payment} specifically
 * for the ecommerce module.
 *
 * @package ecommerce
 */
class EcommercePayment extends DataObjectDecorator {
	
	public static $summary_fields = array(
		"OrderID" => "Order ID",
		"ClassName" => "Type",
		"Amount" => "Amount",
		"Status" => "Status"
	);
		
	function extraStatics() {

		return array(
			'has_one' => array(
				'Order' => 'Order' //redundant...should be using PaidObject
			),
			'summary_fields' => self::$summary_fields,
			'searchable_fields' => array(
				'OrderID' => array(
					'title' => 'Order ID',
					'field' => 'TextField'
				),
				//'Created' => array('title' => 'Date','filter' => 'WithinDateRangeFilter','field' => 'DateRangeField'), //TODO: filter and field not implemented yet				
				'IP' => array(
					'title' => 'IP Address',
					'filter' => 'PartialMatchFilter'
				),
				'Status'
			)
		);
	}

	function canCreate($member = null) {
		return false;
	}

	function canDelete($member = null) {
		return false;
	}

	/*
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
		return $fields;
	}
	*/


	//TODO: this function could get called multiple times, resulting in unwanted logs , changes etc.
	function onAfterWrite() {
		if($this->owner->Status == 'Success' && $order = $this->owner->Order()) {

			if(!$order->ReceiptSent){
				$order->sendReceipt();
				$order->updatePaymentStatus();
			}

		}
	}

	function redirectToOrder() {
		$order = $this->owner->Order();
		Director::redirect($order->Link());
		return;
	}

	function setPaidObject(DataObject $do){
		$this->owner->PaidForID = $do->ID;
		$this->owner->PaidForClass = $do->ClassName;
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

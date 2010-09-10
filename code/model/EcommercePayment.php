<?php
/**
 * Customisations to {@link Payment} specifically
 * for the ecommerce module.
 *
 * @package ecommerce
 */
class EcommercePayment extends DataObjectDecorator {

	protected static $order_status_fully_paid = "Paid";
		static function set_order_status_fully_paid($v) {self::$order_status_fully_paid = $v;}
		static function get_order_status_fully_paid() {return self::$order_status_fully_paid;}

	protected static $payment_status_not_complete = "Incomplete";
		static function set_payment_status_not_complete($v) {self::$payment_status_not_complete = $v;}
		static function get_payment_status_not_complete() {return self::$payment_status_not_complete;}

	protected static $payment_status_success = "Success";
		static function set_payment_status_success($v) {self::$payment_status_success = $v;}
		static function get_payment_status_success() {return self::$payment_status_success;}

	function extraStatics() {
		return array(
			'has_one' => array(
				'Order' => 'Order'
			),
			'searchable_fields' => array(
				'OrderID' => array('title' => 'Order ID'),
				'IP' => array('title' => 'IP Address', 'filter' => 'PartialMatchFilter'),
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
	function updateSummaryFields(&$fields){
		$fields['Created'] = 'Date';
		$fields['OrderID'] = 'OrderID';
		$fields['IP'] = 'Amount';
		$fields['Total'] = 'Total';
	}
	*/

	function onBeforeWrite() {
		if($this->owner->Order()) {
			$id = $this->owner->ID;
			if(!$id) {
				$id = 0;
			}
			$oldData = DataObject::get_by_id("Payment", $id);
			if(!$oldData) {
				$oldData = new Payment();
				$oldStatus = self::get_payment_status_not_complete();
			}
			else {
				$oldStatus = $oldData->Status;
			}
			if($oldStatus != $this->owner->Status && $this->owner->Status == self::get_payment_status_success()) {
				// if the payment status changes  and the payment is successful then send receipt
				$order->sendReceipt();
				//if the payment is set as paid and the order is not marked as paid then this can be done now...
				$order = $this->owner->Order();
				if($order->Status != self::get_order_status_fully_paid()) {
					$order->Status = self::get_order_status_fully_paid();
					$order->write();
				}
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
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		parent::requireDefaultRecords();
		if(isset($_GET["updatepayment"])) {
			DB::query("
				UPDATE {$bt}Payment{$bt}
				SET {$bt}AmountAmount{$bt} = {$bt}Amount{$bt}
				WHERE
					{$bt}Amount{$bt} > 0
					AND (
						{$bt}AmountAmount{$bt} IS NULL
						OR {$bt}AmountAmount{$bt} = 0
					)
			");
			$countAmountChanges = DB::affectedRows();
			if($countAmountChanges) {
				DB::alteration_message("Updated Payment.Amount field to 2.4 - $countAmountChanges rows updated", "edited");
			}
			DB::query("
				UPDATE {$bt}Payment{$bt}
				SET {$bt}AmountCurrency{$bt} = {$bt}Currency{$bt}
				WHERE
					{$bt}Currency{$bt} <> ''
					AND {$bt}Currency{$bt} IS NOT NULL
					AND (
						{$bt}AmountCurrency{$bt} IS NULL
						OR {$bt}AmountCurrency{$bt} = ''
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


}

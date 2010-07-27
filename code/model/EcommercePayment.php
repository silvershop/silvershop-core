<?php
/**
 * Customisations to {@link Payment} specifically
 * for the ecommerce module.
 *
 * @package ecommerce
 */
class EcommercePayment extends DataObjectDecorator {

	function extraStatics() {

		return array(
			'has_one' => array(
				'Order' => 'Order'
			),
			'searchable_fields' => array(
				'OrderID' => array('title' => 'Order ID'),
				'Amount' => array('title' => 'Amount'),
				'IP' => array('title' => 'IP Address', 'filter' => 'PartialMatchFilter'),
				'Status'
			),
			'summary_fields' => array(
				'Status' => "Status",
				'Amount' => 'Amount',
				'XXXP' => 'Currency',
				'IP' => 'IP address',
				'ProxyIP' => 'proxy IP',
				'PaidForID' => "paid for ID",
				'PaidForClass' => 'paid for',
				'PaymentDate' => "Payment Date",
				'ExceptionError' => 'Error'
			)
		);
	}

	function canCreate($member = null) {
		return false;
	}

	function canDelete($member = null) {
		return false;
	}

	function updateSummaryFields(&$fields){
		$fields['Created'] = 'Date';
		$fields['OrderID'] = 'OrderID';
		$fields['Amount'] = 'Amount';
		$fields['IP'] = 'Amount';
		$fields['Total'] = 'Total';
	}

	function onBeforeWrite() {
		if($this->owner->Status == 'Success' && $this->owner->Order()) {
			$order = $this->owner->Order();
			$order->Status = 'Paid';
			$order->write();
			$order->sendReceipt();
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


}

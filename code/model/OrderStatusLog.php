<?php
/**
 * Data class that keeps a log of a single
 * status of an order.
 *
 * @package ecommerce
 */
class OrderStatusLog extends DataObject {

	public static $db = array(
		'Title' => 'Varchar(100)',
		'Note' => 'Text',
		'DispatchedBy' => 'Varchar(100)',
		'DispatchedOn' => 'Date',
		'DispatchTicket' => 'Varchar(100)',
		'PaymentCode' => 'Varchar(100)',
		'PaymentOK' => 'Boolean',
		'SentToCustomer' => 'Boolean'
	);

	public static $has_one = array(
		'Author' => 'Member',
		'Order' => 'Order'
	);

	public function canDelete($member = null) {
		return false;
	}
	public function canEdit($member = null) {
		return false;
	}

	public static $searchable_fields = array(
		"Note" => "PartialMatchFilter",
		'DispatchTicket' => 'PartialMatchFilter',
		'PaymentCode' => 'PartialMatchFilter',
		'PaymentOK'
	);

	public static $summary_fields = array(
		"Created" => "Date",
		"OrderID" => "OrderID",
		"Title" => "Title",
		"SentToCustomer" => "SentToCustomer"
	);

	public static $field_labels = array(
		"SentToCustomer" => "Send this update as a message to the customer"
	);

	public static $singular_name = "Order Log Entry";

	public static $plural_name = "Order Status Log Entries";

	public static $default_sort = "\"Created\" DESC";

	function onBeforeSave() {
		if(!$this->ID) {
			//TO DO - this does not seem to work
			$this->AuthorID = Member::currentUser()->ID;
		}
		parent::onBeforeSave();
	}

	function populateDefaults() {
		parent::populateDefaults();
		$this->updateWithLastInfo();
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->AuthorID && $m = Member::currentUser()) {
			$this->AuthorID = $m->ID;
		}
		if(!$this->Title) {
			$this->Title = "Order Update";
		}
		if(!$this->OrderID) {
			user_error("there is no order id for Order Status Log", E_USER_NOTICE);
		}
	}

	function onAfterWrite(){
		if($this->SentToCustomer) {
			$this->order()->sendStatusChange($this->Title, $this->Note);
		}
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
	}

	protected function updateWithLastInfo() {
		if($this->OrderID) {
			$logs = DataObject::get('OrderStatusLog', "\"OrderID\" = {$this->OrderID}", "\"Created\" DESC", null, 1);
			if($logs) {
				$latestLog = $logs->First();
				$this->DispatchedBy = $latestLog->DispatchedBy;
				$this->DispatchedOn = $latestLog->DispatchedOn;
				$this->DispatchTicket = $latestLog->DispatchTicket;
				$this->PaymentCode = $latestLog->PaymentCode;
				$this->PaymentOK = $latestLog->PaymentOK;
			}
		}
	}

}

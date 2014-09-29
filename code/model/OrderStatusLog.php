<?php
/**
 * Data class that keeps a log of a single
 * status of an order.
 *
 * @package shop
 */
class OrderStatusLog extends DataObject {

	private static $db = array(
		'Title' => 'Varchar(100)',
		'Note' => 'Text',
		'DispatchedBy' => 'Varchar(100)',
		'DispatchedOn' => 'Date',
		'DispatchTicket' => 'Varchar(100)',
		'PaymentCode' => 'Varchar(100)',
		'PaymentOK' => 'Boolean',
		'SentToCustomer' => 'Boolean'
	);

	private static $has_one = array(
		'Author' => 'Member',
		'Order' => 'Order'
	);

	private static $searchable_fields = array(
		"Note" => "PartialMatchFilter",
		'DispatchTicket' => 'PartialMatchFilter',
		'PaymentCode' => 'PartialMatchFilter',
		'PaymentOK'
	);

	private static $summary_fields = array(
		"Created" => "Date",
		"OrderID" => "OrderID",
		"Title" => "Title",
		"SentToCustomer" => "SentToCustomer"
	);

	private static $field_labels = array(
		"SentToCustomer" => "Send this update as a message to the customer"
	);

	private static $singular_name = "Order Log Entry";

	private static $plural_name = "Order Status Log Entries";

	private static $default_sort = "\"Created\" DESC";

	public function canDelete($member = null) {
		return false;
	}
	public function canEdit($member = null) {
		return false;
	}

	public function onBeforeSave() {
		if(!$this->isInDB()) {
			//TO DO - this does not seem to work
			$this->AuthorID = Member::currentUser()->ID;
		}
		parent::onBeforeSave();
	}

	public function populateDefaults() {
		parent::populateDefaults();
		$this->updateWithLastInfo();
	}

	public function onBeforeWrite() {
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

	public function onAfterWrite() {
		if($this->SentToCustomer) {
			$this->order()->sendStatusChange($this->Title, $this->Note);
		}
	}

	protected function updateWithLastInfo() {
		if($this->OrderID) {
			$logs = DataObject::get('OrderStatusLog', "\"OrderID\" = {$this->OrderID}", "\"Created\" DESC", null, 1);
			if($logs && $logs->Count()) {
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

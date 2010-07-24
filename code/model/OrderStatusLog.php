<?php
/**
 * Data class that keeps a log of a single
 * status of an order.
 *
 * @package ecommerce
 */
class OrderStatusLog extends DataObject {

	public static $db = array(
		'Status' => 'Varchar(255)',
		'Note' => 'Text',
		'DispatchedBy' => 'Varchar(255)',
		'DispatchedOn' => 'Date',
		'DispatchTicket' => 'Varchar(255)',
		'PaymentCode' => 'Varchar(255)',
		'PaymentOK' => 'Boolean'
	);

	public static $has_one = array(
		'Author' => 'Member',
		'Order' => 'Order'
	);

	public function canDelete($member = null) {
		return false;
	}

	public static $searchable_fields = array(
		"Note" => "PartialMatchFilter",
		"Status" => "PartialMatchFilter",
		'DispatchTicket' => 'PartialMatchFilter',
		'PaymentCode' => 'PartialMatchFilter',
		'PaymentOK'
	);

	public static $summary_fields = array(
		"Created" => "Date",
		"OrderID" => "OrderID",
		"Status" => "Status",
		"Note" => "Note"
	);

	public static $singular_name = "Order Log Entry";

	public static $plural_name = "Order Status Log Entries";

	public static $default_sort = "Created DESC";

	function onBeforeSave() {
		if(!$this->ID) {
			$this->AuthorID = Member::currentUser()->ID;
		}
		parent::onBeforeSave();
	}


	function requiredDefaultRecords() {
		parent::requiredDefaultRecords();
		//migration of old records
		$oldOnes = DataObject::get("", "SentToCustomer = 1", null, null, "0, 200");
		if($oldOnes) {
			foreach($oldOnes as $oldOne) {
				$oldOne->DispatchedOn = $oldOne->Created;
				$oldOne->SentToCustomer = 0;
				$oldOne->write();
			}
		}
	}

}

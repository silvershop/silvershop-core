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
		'EmailCustomer' => 'Boolean',
		'EmailSent' => 'Boolean'
	);

	public static $has_one = array(
		'Author' => 'Member',
		'Order' => 'Order'
	);

	public static $casting = array(
		"CustomerNote" => "Text"
	);

	public function canDelete($member = null) {
		return false;
	}
	public function canEdit($member = null) {
		return false;
	}

	public static $searchable_fields = array(
		"OrderID" => true,
		"Title" => "PartialMatchFilter",
		"Note" => "PartialMatchFilter"
	);

	public static $summary_fields = array(
		"Created" => "Date",
		"OrderID" => "OrderID",
		"Title" => "Title",
		"EmailSent" => "EmailSent"
	);

	public static $singular_name = "Order Log Entry";

	public static $plural_name = "Order Log Entries";

	public static $default_sort = "\"Created\" DESC";

	function populateDefaults() {
		parent::populateDefaults();
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if($this->ID) {
			user_error("An Order Status Log should only be written once!", E_USER_WARNING);
		}
		if(!$this->OrderID) {
			user_error("There is no order id for Order Status Log", E_USER_WARNING);
		}
		if(!$this->AuthorID && $m = Member::currentUser()) {
			$this->AuthorID = $m->ID;
		}
		if(!$this->Title) {
			$this->Title = "Order Update";
		}
	}

	function onAfterWrite(){
		parent::onAfterWrite();
		if($this->EmailCustomer && !$this->EmailSent) {
			$this->order()->sendStatusChange($this->Title, $this->CustomerNote());
			DB::query("UPDATE \"OrderStatusLog\" SET \"EmailSent\" = 1 WHERE  \"OrderStatusLog\".\"ID\" = ".$this->ID.";");
		}
	}

	function CustomerNote() {
		return $this->Note;
	}

}

class OrderStatusLog_Dispatch extends DataObject {

	public static $db = array(
		'DispatchedBy' => 'Varchar(100)',
		'DispatchedOn' => 'Date',
		'DispatchTicket' => 'Varchar(100)',
	);

	public static $indexes = array(
		"DispatchedOn" => true
	);

	public function canDelete($member = null) {
		return false;
	}
	public function canEdit($member = null) {
		return false;
	}

	public static $searchable_fields = array(
		"Title" => "PartialMatchFilter",
		"Note" => "PartialMatchFilter",
		"DispatchedBy" => "PartialMatchFilter",
		'DispatchTicket' => 'PartialMatchFilter'
	);

	public static $summary_fields = array(
		"DispatchedOn" => "Date",
		"OrderID" => "Order ID",
		"EmailCustomer" => "Customer Emailed"
	);

	public static $singular_name = "Dispatch Entry";

	public static $plural_name = "Dispatch Entries";

	public static $default_sort = "\"DispatchedOn\" DESC";

	function populateDefaults() {
		parent::populateDefaults();
		$sc = DataObject::get_one("SiteConfig");
		if($sc) {
			$this->Title = $sc->DispatchEmailSubject;
		}
		$this->DispatchedOn =  DBField::create('Date', date('Y-m-d'));
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->Title) {
			$sc = DataObject::get_one("SiteConfig");
			if($sc) {
				$this->Title = $sc->DispatchEmailSubject;
			}
		}
		if(!$this->DispatchedOn) {
			$this->DispatchedOn = DBField::create('Date', date('Y-m-d'));
		}
	}

	function CustomerNote() {
		return $this->Note
			."\r\n\r\n"._t("OrderStatusLog.DISPATCHEDBY", "Dispatched By").": ".$this->DispatchedBy
			."\r\n\r\n"._t("OrderStatusLog.DISPATCHEDON", "Dispatched On").": ".$this->DispatchedOn
			."\r\n\r\n"._t("OrderStatusLog.DISPATCHTICKET", "Dispatch Ticket").": ".$this->DispatchTicket;
	}
}

/**
 *@Description: We use this payment check class to double check that payment has arrived against
 * the order placed.  We do this independently of Order as a double-check.  It is important
 * that we do this because the main risk in an e-commerce operation is a fake payment.
 * Any e-commerce operator may set up their own policies on what a payment check
 * entails exactly.  It could include a bank reconciliation or even a phone call to the customer.
 * it is important here that we do not add any payment details. Rather, all we have is a tickbox
 * to state that the checks have been run.

 **/
class OrderStatusLog_PaymentCheck extends DataObject {

	public static $db = array(
		'PaymentConfirmed' => "Boolean",
	);

	public function canDelete($member = null) {
		return false;
	}

	public function canEdit($member = null) {
		return false;
	}

	protected static $true_and_false_definitions = array(
		"yes" => 1,
		"no" => 0
	);
		static function set_true_and_false_definitions($v) {self::$true_and_false_definitions = $v;}
		static function get_true_and_false_definitions() {return self::$true_and_false_definitions;}

	public static $searchable_fields = array(
		"PaymentConfirmed" => true
	);

	public static $summary_fields = array(
		"PaymentConfirmed" => "PaymentConfirmed"
	);

	public static $singular_name = "Payment Confirmation";

	public static $plural_name = "Payment Confirmations";

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("PaymentConfirmed");
		$fields->addFieldsToTab('Root.Main', new TextField("PaymentConfirmed", _t("OrderStatusLog.YESORNO", "Payment has been confirmed (please type yes or no)")));
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->PaymentConfirmed = strtolower($this->PaymentConfirmed);
		if(isset(self::$true_and_false_definitions[$this->PaymentConfirmed])) {
			$this->PaymentConfirmed = self::$true_and_false_definitions[$this->PaymentConfirmed];
		}
		else {
			$this->PaymentConfirmed = 0;
		}
	}
}


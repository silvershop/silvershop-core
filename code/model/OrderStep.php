<?php

/**
 * @description: Defines the Order Status Options
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class OrderStep extends DataObject {
	//database
	public static $db = array(
		"Name" => "Varchar(50)",
		"Code" => "Varchar(50)",
		"Description" => "Text",
		"CustomerMessage" => "HTMLText",
		//customer privileges
		"CanEdit" => "Boolean",
		"CanCancel" => "Boolean",
		"CanPay" => "Boolean",
		//What to show the customer...
		"ShowAsUncompletedOrder" => "Boolean",
		"ShowAsInProcessOrder" => "Boolean",
		"ShowAsCompletedOrder" => "Boolean",
		//sorting index
		"Sort" => "Int",
		//by-pass
		"AutomateThisStatus" => "Boolean"
	);
	public static $indexes = array(
		"Code" => true,
		"Sort" => true
	);
	public static $has_many = array(
		"Orders" => "Order"
	);
	public static $field_labels = array(
	);
	public static $summary_fields = array(
		"Name" => "Name",
		"CanEdit" => "CanEdit",
		"CanCancel" => "CanCancel",
		"CanPay" => "CanPay",
		"ShowAsUncompletedOrder" => "ShowAsUncompletedOrder",
		"ShowAsInProcessOrder" => "ShowAsInProcessOrder",
		"ShowAsCompletedOrder" => "ShowAsCompletedOrder"
	);

	public static $singular_name = "Order Status Option";
		static function get_singular_name() {return self::$singular_name;}
		static function set_singular_name($v) {self::$singular_name = $v;}
		function i18n_singular_name() { return _t("OrderStep.ORDERSTEPOPTION", "Order Status Option");}

	public static $plural_name = "Order Status Options";
		static function get_plural_name() {return self::$plural_name;}
		static function set_plural_name($v) {self::$plural_name = $v;}
		function i18n_plural_name() { return _t("OrderStep.ORDERSTEPOPTION", "Order Status Options");}

	public static $default_sort = "\"Sort\" ASC";

	public static function get_status_id($code) {
		if($otherStatus = DataObject::get_one("OrderStep", "\"Code\" = '".$code."'")) {
			return $otherStatus->ID;
		}
		return 0;
	}

	// MOST IMPORTANT DEFINITION!
	protected static $standard_codes = array(
		"OrderStep_Created" => "CREATED",
		"OrderStep_Submitted" => "SUBMITTED",
		"OrderStep_Paid" => "PAID",
		"OrderStep_Confirmed" => "CONFIRMED",
		"OrderStep_Sent" => "SENT"
	);
		static function set_standard_codes($v) {self::$standard_codes = $v;}
		static function get_standard_codes() {return self::$standard_codes;}


	public static $defaults = array(
		"CanEdit" => 0,
		"CanCancel" => 0,
		"CanPay" =>  0,
		"ShowAsUncompletedOrder" => 0,
		"ShowAsInProcessOrder" => 0,
		"ShowAsCompletedOrder" => 0,
		"AutomateThisStatus" => 0
	);

	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}

	function getCMSFields() {
		//TO DO: add warning messages and break up fields
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING1", _t("OrderStep.CAREFUL", "CAREFUL! please edit with care"), 1), "Name");
		$fields->addFieldToTab("Root.Main", DropdownField("ClassName", _t("OrderStep.TYPE", "Type"), self::$standard_codes));
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING2", _t("OrderStep.CUSTOMERCANCHANGE", "What can be changed?"), 3), "CanEdit");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING5", _t("OrderStep.ORDERGROUPS", "Order groups for customer?"), 3), "ShowAsUncompletedOrder");
		$fields->replaceField("Code", $fields->dataFieldByName("Code")->performReadonlyTransformation());
		if($this->isDefaultStatusOption()) {
			$fields->replaceField("Sort", $fields->dataFieldByName("Code")->performReadonlyTransformation());
		}
		if(!$this->canMakeThisStatusAutomated()) {
			$fields->removeFieldFromTab("Root.Main", "AutomateThisStatus");
		}
		return $fields;
	}

	function validate() {
		$result = DataObject::get_one(
			"OrderStep",
			" (\"Name\" = '".$this->Name."' OR \"Code\" = '".strtoupper($this->Code)."') AND \"OrderStep\".\"ID\" <> ".intval($this->ID));
		if($result) {
			return new ValidationResult((bool) ! $result, _t("OrderStep.ORDERSTEPALREADYEXISTS", "An order status with this name already exists. Please change the name and try again."));
		}
		$result = (bool)($this->ClassName == "OrderStep");
		if($result) {
			return new ValidationResult((bool) ! $result, _t("OrderStep.ORDERSTEPCLASSNOTSELECTED", "You need to select the right order status class."));
		}
		return parent::validate();
	}


/**************************************************
* moving between statusses...
**************************************************/

	public function initStep($order) {
		user_error("Please implement this in a subclass of OrderStep", E_USER_WARNING);
		return true;
	}

	public function ifReadyReturnNextStepObject($order, $codeHint = '') {
		$nextStatus = DataObject::get_one("OrderStep", "\"Sort\" > ".$this->Sort);
		if($codeHint && $codeHint != $nextStatus->Code) {
			return null;
		}
		if($nextStatus) {
			return $nextStatus;
		}
		return null;
	}



/**************************************************
* Boolean checks
**************************************************/

	public function canDelete($member = null) {
		if($order = DataObject::get_one("Order", "StatusID = ".$this->ID)) {
			return false;
		}
		if($this->isDefaultStatusOption()) {
			return false;
		}
		return true;
	}

	protected function canMakeThisStatusAutomated() {
		return false;
	}

	public function hasPassed($code, $orIsEqualTo = false) {
		$otherStatus = DataObject::get_one("OrderStep", "\"Code\" = '".$code."'");
		if($otherStatus) {
			if($otherStatus->Sort < $this->Sort) {
				return true;
			}
			if($orIsEqualTo && $otherStatus->Code == $this->Code) {
				return true;
			}
		}
		else {
			user_error("could not find $code in OrderStep", E_USER_NOTICE);
		}
		return false;
	}

	public function hasPassedOrIsEqualTo($code) {
		return $this->hasPassed($code, true);
	}

	public function hasNotPassed($code) {
		return (bool)!$this->hasPassed($code, true);
	}

	public function isBefore($code) {
		return (bool)!$this->hasPassed($code, false);
	}

	protected function isDefaultStatusOption() {
		return in_array($this->Code, self::$standard_codes);
	}


/**************************************************
* Silverstripe Standard Functions
**************************************************/


	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Code = strtoupper($this->Code);
	}

	function onAfterDelete() {
		parent::onAfterDelete();
		$this->requireDefaultRecords();
	}


	//USED TO BE: Unpaid,Query,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled,Cart
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(self::$standard_codes && count(self::$standard_codes)) {
			foreach(self::$standard_codes as $className => $code) {
				if(!DataObject::get_one("OrderStep", "\"Code\" = '".strtoupper($code)."'")) {
					$obj = new $className();
					$obj->Code = strtoupper($obj->Code);
					$obj->write();
					DB::alteration_message("Created \"$code\" as $className.", "created");
				}
			}
		}
	}
}

class OrderStep_Created extends OrderStep {

	public static $defaults = array(
		"Name" => "Created",
		"Code" => "CREATED",
		"Sort" => 10,
		"CanEdit" => 1,
		"CanCancel" => 1,
		"CanPay" =>  1,
		"ShowAsUncompletedOrder" => 1
	);

	protected function canMakeThisStatusAutomated() {
		return true;
	}

	public function initStep($order) {
		return true;
	}

	public function ifReadyReturnNextStepObject($order, $codeHint = "") {
		$newStatus = parent::ifReadyReturnNextStepObject($order, $codeHint = "");
		if($this->AutomateThisStatus) {
			return $newStatus;
		}
		if($order->Items()) {
			return $newStatus;
		}
		return null;
	}


	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStep_Submitted extends OrderStep {

	static $defaults = array(
		"Name" => "Submitted",
		"Code" => "SUBMITTED",
		"Sort" => 20,
		"CanPay" =>  1,
		"ShowAsInProcessOrder" => 1
	);

	protected function canMakeThisStatusAutomated() {
		return false;
	}


	public function initStep($order) {

		//re-write all attributes and modifiers to make sure they are up-to-date before they can't be changed again
		$order->calculateModifiers();
		if(!$order->MemberID) {
			$order->MemberID = Member::currentUserID();
		}
		if(!$order->MemberID) {
			user_error("Can not submit an order without a customer.", E_USER_ERROR);
		}
		$order->MemberID = $member->ID;
		$siteConfig = DataObject::get_one("SiteConfig");
		if($siteConfig && $siteConfig->SendInvoiceOnSubmit) {
			if(!$order->InvoiceSent){
				$order->sentInvoice();
			}
		}
		$this->extend('onSubmit', $member);
		return true;
	}

	public function ifReadyReturnNextStepObject($order, $codeHint = "") {
		$newStatus = parent::ifReadyReturnNextStepObject($order, $codeHint = "");
		if($this->AutomateThisStatus) {
			return $newStatus;
		}
		if($order->MemberID) {
			return $newStatus;
		}
		return null;
	}

	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}

}

class OrderStep_Paid extends OrderStep {

	static $db = array(
		"SendReceiptOnPaid" => "Boolean",
		"ReceiptSent" => "Boolean"
	);

	public static $defaults = array(
		"Name" => "Paid",
		"Code" => "PAID",
		"Sort" => 30,
		"ShowAsInProcessOrder" => 1
	);

	protected function canMakeThisStatusAutomated() {
		return false;
	}

	public function initStep($order) {
		if($siteConfig && $this->SendReceiptOnPaid) {
			if(!$this->ReceiptSent){
				$order->sendReceipt();
			}
		}
		$this->extend('onPay', $payments);
		return true;
	}

	public function ifReadyReturnNextStepObject($order, $codeHint = "") {
		$newStatus = parent::ifReadyReturnNextStepObject($order, $codeHint = "");
		if($this->AutomateThisStatus) {
			return $newStatus;
		}
		if($order->IsPaid()) {
			return $newStatus;
		}
		return null;
	}


	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStep_Confirmed extends OrderStep {

	public static $defaults = array(
		"Name" => "Confirmed",
		"Code" => "CONFIRMED",
		"Sort" => 40,
		"ShowAsInProcessOrder" => 1
	);

	protected function canMakeThisStatusAutomated() {
		return true;
	}

	public function ifReadyReturnNextStepObject($order, $codeHint = "") {
		$newStatus = parent::ifReadyReturnNextStepObject($order, $codeHint = "");
		if($this->AutomateThisStatus) {
			return $newStatus;
		}
		if($order->HasPositivePaymentCheck()) {
			return $newStatus;
		}
		return null;
	}

	public function initStep($order) {
		if(!$order->ReceiptSent){
			$order->sendReceipt();
		}
		$this->extend('onConfirmed', $log);
	}

	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStep_Sent extends OrderStep {

	public static $defaults = array(
		"Name" => "Sent",
		"Code" => "SENT",
		"Sort" => 50,
		"ShowAsCompletedOrder" => 1
	);

	protected function canMakeThisStatusAutomated() {
		return true;
	}

	public function ifReadyReturnNextStepObject($order, $codeHint = "") {
		$newStatus = parent::ifReadyReturnNextStepObject($order, $codeHint = "");
		if($this->AutomateThisStatus) {
			return $newStatus;
		}
		if($order->HasDispatchRecord()) {
			return $newStatus;
		}
		return null;
	}
	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}



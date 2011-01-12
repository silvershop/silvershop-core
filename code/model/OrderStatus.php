<?php

/**
 * @description: Defines the Order Status Options
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class OrderStatus extends DataObject {
	//database
	public static $db = array(
		"Name" => "Varchar(50)",
		"Code" => "Varchar(50)",
		"Description" => "Text",
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
		"AutomateThisStep" => "Boolean"
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
		function i18n_singular_name() { return _t("OrderStatus.ORDERSTATUSOPTION", "Order Status Option");}

	public static $plural_name = "Order Status Options";
		static function get_plural_name() {return self::$plural_name;}
		static function set_plural_name($v) {self::$plural_name = $v;}
		function i18n_plural_name() { return _t("OrderStatus.ORDERSTATUSOPTIONS", "Order Status Options");}

	public static $default_sort = "\"Sort\" ASC";

	public static function get_status_id($code) {
		if($otherStatus = DataObject::get_one("OrderStatus", "\"Code\" = '".$code."'")) {
			return $otherStatus->ID;
		}
		return 0;
	}

	protected static $standard_codes = array(
		"OrderStatus_Created" => "CREATED",
		"OrderStatus_Submitted" => "SUBMITTED",
		"OrderStatus_Paid" => "PAID",
		"OrderStatus_Confirmed" => "CONFIRMED",
		"OrderStatus_Sent" => "SENT"
	);

	public static $defaults = array(
		"CanEdit" => 0,
		"CanCancel" => 0,
		"CanPay" =>  0,
		"ShowAsUncompletedOrder" => 0,
		"ShowAsInProcessOrder" => 0,
		"ShowAsCompletedOrder" => 0,
		"AutomateThisStep" => 0
	);

	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}

	public function hasPassed($code, $orIsEqualTo = false) {
		$otherStatus = DataObject::get_one("OrderStatus", "\"Code\" = '".$code."'");
		if($otherStatus) {
			if($otherStatus->Sort < $this->Sort) {
				return true;
			}
			if($orIsEqualTo && $otherStatus->Code == $this->Code) {
				return true;
			}
		}
		else {
			user_error("could not find $code in OrderStatus", E_USER_NOTICE);
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

	function getCMSFields() {
		//TO DO: add warning messages and break up fields
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING1", _t("OrderStatus.CAREFUL", "CAREFUL! please edit with care"), 1), "Name");
		$fields->addFieldToTab("Root.Main", DropdownField("ClassName", _t("OrderStatus.TYPE", "Type"), self::$standard_codes));
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING2", _t("OrderStatus.CUSTOMERCANCHANGE", "What can be changed?"), 3), "CanEdit");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING5", _t("OrderStatus.ORDERGROUPS", "Order groups for customer?"), 3), "ShowAsUncompletedOrder");
		$fields->replaceField("Code", $fields->dataFieldByName("Code")->performReadonlyTransformation());
		if($this->isDefaultStatusOption()) {
			$fields->replaceField("Sort", $fields->dataFieldByName("Code")->performReadonlyTransformation());
		}
		if(!$this->canMakeThisStepAutomated()) {
			$fields->removeFieldFromTab("Root.Main", "AutomateThisStep");
		}
		return $fields;
	}

	function validate() {
		$result = DataObject::get_one($this->ClassName, "\"Name\" = '".$this->Name."' AND \"OrderStatus\".\"ID\" <> ".intval($this->ID));
		if($result) {
			return new ValidationResult((bool) ! $result, _t("OrderStatus.ORDERSTATUSALREADYEXISTS", "An order status with this name already exists. Please change the name and try again."));
		}
		$result = (bool)($this->ClassName == "OrderStatus");
		if($result) {
			return new ValidationResult((bool) ! $result, _t("OrderStatus.ORDERSTATUSCLASSNOTSELECTED", "You need to select the right order status class."));
		}
		return parent::validate();
	}

	public function canDelete($member = null) {
		if($order = DataObject::get_one("Order", "StatusID = ".$this->ID)) {
			return false;
		}
		if($this->isDefaultStatusOption()) {
			return false;
		}
		return true;
	}

	protected function canMakeThisStepAutomated() {
		return false;
	}

	public function ifReadyReturnNewStatusID($nextCode, $order) {
		if($this->isBefore($nextCode)) {
			return self::get_status_id($nextCode);
		}
		return 0;
	}

	function onAfterDelete() {
		parent::onAfterDelete();
		$this->requireDefaultRecords();
	}


	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Code = strtoupper($this->Code);
	}

	protected function isDefaultStatusOption() {
		return in_array($this->Code, self::$standard_codes);
	}

	//Unpaid,Query,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled,Cart
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(self::$standard_codes && count(self::$standard_codes)) {
			foreach(self::$standard_codes as $className => $code) {
				if(!DataObject::get_one("OrderStatus", "\"Code\" = '".$code."'")) {
					$obj = new $className();
					$obj->write();
					DB::alteration_message("Created \"$code\" as $className.", "created");
				}
			}
		}
	}
}

class OrderStatus_Created extends OrderStatus {

	public static $defaults = array(
		"Name" => "Created",
		"Code" => "CREATED",
		"Sort" => 10,
		"CanEdit" => 1,
		"CanCancel" => 1,
		"CanPay" =>  1,
		"ShowAsUncompletedOrder" => 1
	);

	protected function canMakeThisStepAutomated() {
		return true;
	}

	public function ifReadyReturnNewStatusID($nextCode, $order) {
		$newStatus = parent::ifReadyReturnNewStatusID($nextCode, $order);
		if($this->AutomateThisStep) {
			return $newStatus;
		}
		if($order->Items()) {
			return $newStatus;
		}
		return 0;
	}
	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStatus_Submitted extends OrderStatus {

	static $defaults = array(
		"Name" => "Submitted",
		"Code" => "SUBMITTED",
		"Sort" => 20,
		"CanPay" =>  1,
		"ShowAsInProcessOrder" => 1
	);

	protected function canMakeThisStepAutomated() {
		return false;
	}

	public function ifReadyReturnNewStatusID($nextCode, $order) {
		$newStatus = parent::ifReadyReturnNewStatusID($nextCode, $order);
		if($this->AutomateThisStep) {
			return $newStatus;
		}
		if($order->MemberID) {
			return $newStatus;
		}
		return 0;
	}
	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStatus_Paid extends OrderStatus {

	public static $defaults = array(
		"Name" => "Paid",
		"Code" => "PAID",
		"Sort" => 30,
		"ShowAsInProcessOrder" => 1
	);

	protected function canMakeThisStepAutomated() {
		return false;
	}

	public function ifReadyReturnNewStatusID($nextCode, $order) {
		$newStatus = parent::ifReadyReturnNewStatusID($nextCode, $order);
		if($this->AutomateThisStep) {
			return $newStatus;
		}
		if($order->IsPaid()) {
			return $newStatus;
		}
		return 0;
	}
	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStatus_Confirmed extends OrderStatus {

	public static $defaults = array(
		"Name" => "Confirmed",
		"Code" => "CONFIRMED",
		"Sort" => 40,
		"ShowAsInProcessOrder" => 1
	);

	protected function canMakeThisStepAutomated() {
		return true;
	}


	public function ifReadyReturnNewStatusID($nextCode, $order) {
		$newStatus = parent::ifReadyReturnNewStatusID($nextCode, $order);
		if($this->AutomateThisStep) {
			return $newStatus;
		}
		if($order->HasPositivePaymentCheck()) {
			return $newStatus;
		}
		return 0;
	}
	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}

class OrderStatus_Sent extends OrderStatus {

	public static $defaults = array(
		"Name" => "Sent",
		"Code" => "SENT",
		"Sort" => 50,
		"ShowAsCompletedOrder" => 1
	);

	protected function canMakeThisStepAutomated() {
		return true;
	}

	public function ifReadyReturnNewStatusID($nextCode, $order) {
		$newStatus = parent::ifReadyReturnNewStatusID($nextCode, $order);
		if($this->AutomateThisStep) {
			return $newStatus;
		}
		if($order->HasDispatchRecord()) {
			return $newStatus;
		}
		return 0;
	}
	function populateDefaults() {
		parent::populateDefaults();
		foreach(self::$defaults as $field => $value) {
			$this->$field = $value;
		}
	}
}



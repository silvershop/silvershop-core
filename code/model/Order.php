<?php

/**
 * @description: The order class is a databound object for handling Orders within SilverStripe.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class Order extends DataObject {

	public static $db = array(
		'SessionID' => "Varchar(32)", //so that in the future we can link sessions with Orders.... One session can have several orders, but an order can onnly have one session
		'Country' => 'Varchar(3)',
		'UseShippingAddress' => 'Boolean',
		'CustomerOrderNote' => 'Text'
	);

	public static $has_one = array(
		'Member' => 'Member',
		'ShippingAddress' => 'ShippingAddress',
		'Status' => 'OrderStep',
		'CancelledBy' => 'Member'
	);

	public static $has_many = array(
		'Attributes' => 'OrderAttribute',
		'OrderStatusLogs' => 'OrderStatusLog',
		'Payments' => 'Payment',
		'Emails' => 'OrderEmailRecord'
	);

	public static $many_many = array();

	public static $belongs_many_many = array();

	public static $defaults = array();

	public static $indexes = array(
		"SessionID" => true
	);

	public static $default_sort = "\"Created\" DESC";

	public static $casting = array(
		'Title' => 'Text',
		'Total' => 'Currency',
		'SubTotal' => 'Currency',
		'TotalPaid' => 'Currency',
		'Shipping' => 'Currency',
		'TotalOutstanding' => 'Currency',
		'TotalItems' => 'Int',
		'TotalItemsTimesQuantity' => 'Int',
		'IsCancelled' => 'Boolean'
	);

	public static $create_table_options = array(
		'MySQLDatabase' => 'ENGINE=InnoDB'
	);

	public static $singular_name = "Order";
		function i18n_singular_name() { return _t("Order.ORDER", "Order");}

	public static $plural_name = "Orders";
		function i18n_plural_name() { return _t("Order.ORDERS", "Orders");}

	static function get_receipt_email() {$sc = DataObject::get_one("SiteConfig"); if($sc && $sc->ReceiptEmail) {return $sc->ReceiptEmail;} else {return Email::getAdminEmail();} }

	static function get_receipt_subject() {$sc = DataObject::get_one("SiteConfig"); if($sc && $sc->ReceiptSubject) {return $sc->ReceiptSubject;} else {return "Shop Sale Information {OrderNumber}"; } }

	/**
	 * Modifiers represent the additional charges or
	 * deductions associated to an order, such as
	 * shipping, taxes, vouchers etc.
	 *
	 * @var array
	 */
	protected static $modifiers = array();

	/**
	 * Set the modifiers that apply to this site.
	 *
	 * @param array $modifiers An array of {@link OrderModifier} subclass names
	 */
	public static function set_modifiers($modifiers, $replace = false) {
		if($replace) {
			self::$modifiers = $modifiers;
		}
		else {
			self::$modifiers =  array_merge(self::$modifiers,$modifiers);
		}
	}

	/**
	 * Return a set of forms to add modifiers
	 * to update the OrderInformation table.
	 *
	 * @TODO Make the above descrption clearer
	 * after fully understanding what this
	 * function does.
	 *
	 * @return DataObjectSet
	 */
	public static function get_modifier_forms($controller) {
		$forms = array();
		if(self::$modifiers && is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $className) {
				if(class_exists($className)) {
					$modifier = new $className();
					if($modifier instanceof OrderModifier && eval("return $className::show_form();") && $form = eval("return $className::get_form(\$controller);")) {
						array_push($forms, $form);
					}
				}
			}
		}
		if( count($forms) ) {
			return new DataObjectSet($forms);
		}
		else {
			return null;
		}
	}

	protected static $maximum_ignorable_sales_payments_difference = 0.01;
		static function set_maximum_ignorable_sales_payments_difference($v) {self::$maximum_ignorable_sales_payments_difference = $v;}
		static function get_maximum_ignorable_sales_payments_difference() {return self::$maximum_ignorable_sales_payments_difference;}

 	protected static $order_id_start_number = 0;
		static function set_order_id_start_number($v) {self::$order_id_start_number = $v;}
		static function get_order_id_start_number() {return self::$order_id_start_number;}

	public static function get_order_status_options() {
		return DataObject::get("OrderStep");
	}

	public static function get_by_id($id) {
		$obj = DataObject::get_by_id("Order", $id);
		if($obj->canView()) {
			return $obj;
		}
		return null;
	}
	public static function get_by_id_and_member_id($id, $memberID) {
		$obj = DataObject::get_by_id("Order", $id);
		if($obj) {
			if($obj->MemberID == $memberID && $obj->canView()) {
				return $obj;
			}
		}
		return null;
	}

/*******************************************************
   * CMS Stuff
*******************************************************/

	/**
	 * These are the fields, used for a {@link ComplexTableField}
	 * in order to show for the table columns on a report.
	 *
	 * @see CurrentOrdersReport
	 * @see UnprintedOrdersReport
	 *
	 * To customise these, simply define Order::set_table_overview_fields(Array)
	 * inside your project _config.php where Array is a set of fields that
	 * you want to display on the table.
	 *
	 * @var array
	 */
	protected static $table_overview_fields = array(
		'ID' => 'Order No',
		'Created' => 'Created',
		'Member.FirstName' => 'First Name',
		'Member.Surname' => 'Surname',
		'Total' => 'Total',
		'Status' => 'Status'
	);
		public static function set_table_overview_fields($fields) {self::$table_overview_fields = $fields;}
		public static function get_table_overview_fields() {return self::$table_overview_fields;}

	public static $summary_fields = array(
		"Title" => "Summary",
		'Member.Surname',
		'Member.Email',
		'Total' => 'Total',
		'TotalOutstanding' => 'Outstanding',
		'Status.Name',
	);

	public static $searchable_fields = array(
		'ID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		'Printed',
		'Member.FirstName' => array(
			'title' => 'Customer First Name',
			'filter' => 'PartialMatchFilter'
		),
		'Member.Surname' => array(
			'title' => 'Customer Last Name',
			'filter' => 'PartialMatchFilter'
		),
		'Member.Email' => array(
			'title' => 'Customer Email',
			'filter' => 'PartialMatchFilter'
		),
		'Member.Phone' => array(
			'title' => 'Customer Phone',
			'filter' => 'PartialMatchFilter'
		),
		'Created' => array(
			'field' => 'TextField',
			'filter' => 'OrderFilters_AroundDateFilter',
			'title' => "Date"
		),
		'TotalPaid' => array(
			'filter' => 'OrderFilters_MustHaveAtLeastOnePayment'
		),
		'StatusID' => array(
			'filter' => 'OrderFilters_MultiOptionsetStatusIDFilter'
		),
		'CancelledByID' => array(
			'filter' => 'OrderFilters_HasBeenCancelled',
			'title' => "Cancelled"
		)
		/*,
		'To' => array(
			'field' => 'DateField',
			'filter' => 'OrderFilters_EqualOrSmallerDateFilter'
		)
		*/
	);

	function scaffoldSearchFields(){
		$fieldSet = parent::scaffoldSearchFields();
		if($statusOptions = self::get_order_status_options()) {
			$fieldSet->push(new CheckboxSetField("StatusID", "Status", $statusOptions->toDropDownMap()));
		}
		$fieldSet->push(new DropdownField("TotalPaid", "Has Payment", array(-1 => "(Any)", 1 => "yes", 0 => "no")));
		$fieldSet->push(new DropdownField("CancelledByID", "Cancelled", array(-1 => "(Any)", 1 => "yes", 0 => "no")));
		return $fieldSet;
	}

	function validate() {
		if(!$this->ID || $this->StatusID) {
			return parent::validate();
		}
		else {
			return new ValidationError(false, _t("Order.MUSTSETSTATUS", "You must set a status"));
		}
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$readOnly = (bool)!$this->canEdit();
		$fieldsAndTabsToBeRemoved = array('Printed', 'MemberID', 'Attributes', 'SessionID', 'Country', 'ShippingAddressID', 'UseShippingAddress', 'OrderStatusLogs');
		if(!$readOnly) {
			$fieldsAndTabsToBeRemoved[] = "Payments";
			$fieldsAndTabsToBeRemoved[] = "Emails";
		}
		foreach($fieldsAndTabsToBeRemoved as $field) {
			$fields->removeByName($field);
		}
		$fields->insertBefore(new LiteralField('Title',"<h2>".$this->Title()."</h2>"),'Root');
		if($readOnly) {
			$htmlSummary = $this->renderWith("Order");
			$printlabel = (!$this->Printed) ? "Print Invoice" : "Print Invoice Again"; //TODO: i18n
			$fields->addFieldsToTab('Root.Main', array(
				new LiteralField("PrintInvoice",'<p class="print"><a href="OrderReport_Popup/index/'.$this->ID.'?print=1" onclick="javascript: window.open(this.href, \'print_order\', \'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50\'); return false;">'.$printlabel.'</a></p>')
			));

			$fields->addFieldToTab('Root.Main', new LiteralField('MainDetails', $htmlSummary));
		}
		else {
			$fields->addFieldToTab('Root.Main', new LiteralField('MainDetails', _t("Order.NODETAILSSHOWN", '<p>No details are shown here as this order has not been submitted yet. Once you change the status of the order more options will be available.</p>')));
		}
		//TODO: re-introduce this when order status logs have some meaningful purpose
		//$fields->removeByName('OrderStatusLogs');

		$orderItemsTable = new HasManyComplexTableField(
			$this, //$controller
			"Attributes", //$name =
			"OrderItem", //$sourceClass =
			null, //$fieldList =
			null, //$detailedFormFields =
			"\"OrderID\" = ".$this->ID."  AND \"ClassName\" = 'OrderItem'", //$sourceFilter =
			"\"Created\" ASC", //$sourceSort =
			null //$sourceJoin =
		);
		if(!$readOnly) {
			$orderItemsTable->setPermissions(array('edit', 'delete', 'export', 'add', 'inlineadd', "show"));
		}
		else {
			$orderItemsTable->setPermissions(array("show"));
		}
		$orderItemsTable->setShowPagination(false);
		$orderItemsTable->addSummary(
			"Total",
			array("Total" => array("sum","Currency->Nice"))
		);

		$fields->addFieldToTab('Root.Items',$orderItemsTable);

		$modifierTable = new TableListField(
			"OrderModifiers", //$name
			"OrderModifier", //$sourceClass =
			OrderModifier::$summary_fields, //$fieldList =
			"\"OrderID\" = ".$this->ID."", //$sourceFilter =
			"\"Type\", \"Amount\" ASC, \"Created\" ASC", //$sourceSort =
			null //$sourceJoin =
		);
		if(!$readOnly) {
			$modifierTable->setPermissions(array('edit', 'delete', 'export', 'add', 'inlineadd', "show"));
		}
		else {
			$modifierTable->setPermissions(array("show"));
		}
		$modifierTable->setPageSize(100);
		$fields->addFieldToTab('Root.Extras',$modifierTable);
		if($readOnly) {
			if($m = $this->Member()) {
				$lastLogin = new TextField("MemberLastLogin","Last login",$m->dbObject('LastVisited')->Nice());
				$fields->addFieldToTab('Root.Customer',$lastLogin->performReadonlyTransformation());
				//TODO: this should be scaffolded instead, or come from something like $member->getCMSFields();
				$fields->addFieldToTab('Root.Customer',new LiteralField("MemberSummary", $m->renderWith("Order_Member")));
			}
			$this->extend('updateCMSFields',$fields);
			$fields->addFieldsToTab(
				"Root.Delivery",
				array(
					new CheckboxField("UseShippingAddress", "Shipping Address is not the same as Billing Address"),
					new HeaderField("DispatchLog", _t("Order.DISPATCHLOG", "Dispatch Log")),
					new ComplexTableField($controller = "OrderStatusLog_Dispatch", "OrderStatusLog_Dispatch", "OrderStatusLog_Dispatch", $fieldList = null, $detailFormFields = null, $sourceFilter = "\"OrderID\" = ".$this->ID, $sourceSort = "", $sourceJoin = "")
				)
			);
		}
		return $fields;
	}


/*******************************************************
   * MAIN TRANSITION FUNCTIONS:
*******************************************************/

	public function tryToFinaliseOrder() {
		$this->init();
		for($i = 1; $i < 99; $i++) {
			if(!$this->doNextStatus()) {
				$i = 100;
			}
		}
	}

	public function doNextStatus() {
		if($this->MyStatus()->initStep($this)) {
			if($this->MyStatus()->doStep($this)) {
				if($nextOrderStepObject = $this->MyStatus()->nextStep($this)) {
					$this->StatusID = $nextOrderStepObject->ID;
					$this->write();
					return $this->StatusID;
				}
			}
		}
		return false;
	}

	// ------------------------------------ STEP 1 ------------------------------------

	//NOTE: anything to do with Current Member and Session should be in Shopping Cart!
	public function init() {
		//to do: check if shop is open....
		$this->initModifiers();
		if(!$this->StatusID) {
			if($newStatus = DataObject::get_one("OrderStep")) {
				$this->StatusID = $newStatus->ID;
			}
			else {
				user_error("There are no OrderSteps ... please Run Dev/Build", E_USER_WARNING);
			}
		}
		$this->extend('onInit');
		$this->write();
		return $this;
	}


/*******************************************************
   * STATUS RELATED FUNCTIONS / SHORTCUTS
*******************************************************/
	public function MyStatus() {
		$obj = null;
		if($this->StatusID) {
			$obj = DataObject::get_by_id("OrderStep", $this->StatusID);
		}
		if(!$obj) {
			$obj = DataObject::get_one("OrderStep");
		}
		$this->StatusID = $obj->ID;
		return $obj;
	}
	/**
	 * @return boolean
	 */
	function IsCancelled() {
		return (bool)$this->CancelledByID;
	}
	/**
	 * @return boolean
	 */
	function IsUncomplete() {
		return (bool)$this->MyStatus()->ShowAsUncompletedOrder;
	}
	/**
	 * @return boolean
	 */
	function IsProcessing() {
		return (bool)$this->MyStatus()->ShowAsInProcessOrder;
	}
	/**
	 * @return boolean
	 */
	function IsCompleted() {
		return (bool)$this->MyStatus()->ShowAsCompletedOrder;
	}
	/**
	 * @return boolean
	 */
	function IsPaid() {
		return (bool)($this->Total() > 0 && $this->TotalOutstanding() <= 0);
	}
	/**
	 * @return boolean
	 */
	function IsCart(){
		return (bool)$this->canEdit();
	}
	/**
	 * @return boolean
	 */
	function IsCustomerCancelled() {
		if($this->MemberID == $this->IsCancelledID && $this->MemberID > 0) {
			return true;
		}
		return false;
	}
	/**
	 * @return boolean
	 */
	function MemberCancelled() {
		return $this->CustomerCancelled();
	}

	/**
	 * @return boolean
	 */
	function IsAdminCancelled() {
		if($this->IsCancelled()) {
			if(!$this->IsCustomerCancelled()) {
				$admin = DataObject::get_by_id("Member", $this->CancelledByID);
				if($admin->IsShopAdmin()) {
					return true;
				}
				return null;
			}
		}
		return false;
	}

	/**
	 * @return boolean
	 */
	function HasPositivePaymentCheck() {
		return DataObject::get_one("OrderStatusLog_PaymentCheck", "\"OrderID\" = ".$this->ID." AND \"PaymentConfirmed\" = 1");
	}

	/**
	 * @return boolean
	 */
	function HasDispatchRecord() {
		return DataObject::get_one("OrderStatusLog_Dispatch", "\"OrderID\" = ".$this->ID);
	}

	/**
	 * @return boolean
	 */
	function ShopClosed() {
		$siteConfig = DataObject::get_one("SiteConfig");
		return $siteConfig->ShopClosed;
	}
/*******************************************************
   * CUSTOMER COMMUNICATION....
*******************************************************/

	function sendInvoice() {
		$subject = str_replace("{OrderNumber}", $this->ID,self::get_receipt_subject());
		$replacementArray = array(
			'Order' => $this
		);
		return $this->sendEmail('Order_ReceiptEmail', $subject, $replacementArray, true);
	}
  	/**
	 * Send the receipt of the order by mail.
	 * Precondition: The order payment has been successful
	 */
	function sendReceipt() {
		$subject = str_replace("{OrderNumber}", $this->ID,self::get_receipt_subject());
		$purchaseCompleteMessage = DataObject::get_one('CheckoutPage')->PurchaseComplete;
		$replacementArray = array(
			'PurchaseCompleteMessage' => $purchaseCompleteMessage,
			'Order' => $this
		);
		return $this->sendEmail('Order_ReceiptEmail', $subject, $replacementArray, true);
	}

	/**
	 * Send a message to the client containing the latest
	 * note of {@link OrderStatusLog} and the current status.
	 *
	 * Used in {@link OrderReport}.
	 *
	 * @param string $note Optional note-content (instead of using the OrderStatusLog)
	 */
	function sendStatusChange($subject, $note = null) {
		if(!$note) {
			$logs = DataObject::get('OrderStatusLog', "\"OrderID\" = {$this->ID} AND \"EmailCustomer\" = 1 AND \"EmailSent\" = 0 ", "\"Created\" DESC", null, 1);
			if($logs) {
				$latestLog = $logs->First();
				$note = $latestLog->Note;
			}
		}
		$replacementArray =
			array(
				"Order" => $this,
				"Member" => $member,
				"Note" => $note
			);
		return $this->sendEmail('Order_StatusEmail', $subject, $replacementArray, true);
	}


	/**
	 * Send a mail of the order to the client (and another to the admin).
	 *
	 * @param $emailClass - the class name of the email you wish to send
	 * @param $subject - email subject
	 * @param $replacementArray - array of fields to replace with data...
	 * @param $copyToAdmin - true by default, whether it should send a copy to the admin
	 */
	protected function sendEmail($emailClass, $subject, $replacementArray = array(), $copyToAdmin = true) {
 		$from = self::get_receipt_email();
 		$to = $this->Member()->Email;
		//TO DO: should be a payment specific message as well???
		$email = new $emailClass();
		if(!($emailClass instanceOf Email)) {
			user_error("No correct email class provided.", E_USER_ERROR);
		}
 		$email->setFrom($from);
 		$email->setTo($to);
 		$email->setSubject($subject);
		if($copyToAdmin) {
			$email->setBcc(Email::getAdminEmail());
		}
		$email->populateTemplate(
			$replacementArray
		);
		return $email->send(null, $this);
	}

/*******************************************************
   * ITEM MANAGEMENT
*******************************************************/

	/**
	 * Returns the items of the order, if it hasn't been saved yet
	 * it returns the items from session, if it has, it returns them
	 * from the DB entry.
	 */
	function Items($filter = "") {
 		if(!$this->ID){
 			$this->write();
		}
		return $this->itemsFromDatabase($filter);
	}

	/**
	 * Return all the {@link OrderItem} instances that are
	 * available as records in the database.
	 *
	 * @return DataObjectSet
	 */
	protected function itemsFromDatabase($filter = null) {
		$extrafilter = ($filter) ? " AND $filter" : "";
		$items = DataObject::get("OrderItem", "\"OrderID\" = '$this->ID' $extrafilter");
		return $items;
	}


	/**
	 * Initialise all the {@link OrderModifier} objects
	 * by evaluating init_for_order() on each of them.
	 */
	protected function initModifiers() {
		//check if order has modifiers already
		//check /re-add all non-removable ones
		$createdmodifiers = $this->Modifiers();
		if(self::$modifiers && is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $key => $className) {
				if(class_exists($className) && (!$createdmodifiers || !$createdmodifiers->find('ClassName',$className))) {
					$modifier = new $className();
					if($modifier instanceof OrderModifier) {
						$modifier->OrderID = $this->ID;
						$modifier->Sort = $key;
						$modifier->init();
						$modifier->write();
						$this->Attributes()->add($modifier);
					}
				}
			}
		}
		$this->extend("onInitModifiers");
	}


	/**
	 * Returns the modifiers of the order, if it hasn't been saved yet
	 * it returns the modifiers from session, if it has, it returns them
	 * from the DB entry.
	 */
 	function Modifiers() {
 		if(!$this->ID) {
 			$this->write();
			$this->initModifiers();
 		}
		return $this->modifiersFromDatabase();
	}

	/**
	 * Get all {@link OrderModifier} instances that are
	 * available as records in the database.
	 *
	 * @return DataObjectSet
	 * todo: add filter...
	 */
	protected function modifiersFromDatabase() {
		return DataObject::get('OrderModifier', $where = "\"OrderAttribute\".\"OrderID\" = ".$this->ID, $sort = "\"OrderAttribute\".\"Sort\" ASC");
	}

	public function calculateModifiers() {
		$this->initModifiers();
		$modifiers = $this->Modifiers();
		if($modifiers) {
			if($modifiers->exists()){
				foreach($modifiers as $modifier){
					$modifiers->write();
				}
			}
		}
		$this->extend("onCalculate");
	}

	/**
	 * Returns a TaxModifier object that provides
	 * information about tax on this order.
	 *
	 * @return TaxModifier
	 */
	function TaxInfo() {
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if($modifier instanceof TaxModifier) {
					return $modifier;
				}
			}
		}
	}


/*******************************************************
   * CRUD METHODS (e.g. canView, canEdit, canDelete, etc...)
*******************************************************/

	public function canCreate($member = null) {
		if(!$member) {$member = Member::currentMember();}
		if($member) {$memberID = $member->ID;} else {$memberID = 0;}
		$extended = $this->extendedCan('canCreate', $memberID);
		if($extended !== null) {return $extended;}
		//TO DO: setup a special group of shop admins (probably can copy some code from Blog)
		if($member) {
			return $member->IsShopAdmin();
		}
	}
	public function canView($member = null) {
		if(!$member) {$member = Member::currentMember();}
		if($member) {$memberID = $member->ID;} else {$memberID = 0;}
		$extended = $this->extendedCan('canView', $memberID);
		if($extended !== null) {return $extended;}
		if(!$this->MemberID) {
			return true;
		}
		if($member) {
			if($this->MemberID == $member->ID) {
				if($this->IsCancelled()) {
					return false;
				}
				return true;
			}
			//TO DO: IsAdmin Should be IsShopAdmin
			elseif($member->IsShopAdmin()) {
				return true;
			}
		}
		return false;
	}


	function canEdit($member = null) {
		if(!$member) {$member = Member::currentMember();}
		if($member) {$memberID = $member->ID;} else {$memberID = 0;}
		$extended = $this->extendedCan('canEdit', $memberID);
		if($extended !== null) {return $extended;}
		if(!$this->canView($member)) {
			return false;
		}
		return $this->MyStatus()->CanEdit;
	}

	function canPay($member = null) {
		if(!$member) {$member = Member::currentMember();}
		if($member) {$memberID = $member->ID;} else {$memberID = 0;}
		$extended = $this->extendedCan('canPay', $memberID);
		if($extended !== null) {return $extended;}
		if($this->IsPaid() || $this->IsCancelled()) {
			return false;
		}
		return true;
	}
	function canCancel($member = null) {
		if($this->CancelledByID) {
			return true;
		}
		if(!$member) {$member = Member::currentMember();}
		if($member) {$memberID = $member->ID;} else {$memberID = 0;}
		$extended = $this->extendedCan('canCancel', $memberID);
		if($extended !== null) {return $extended;}
		if($member) {
			if($member->IsShopAdmin()) {
				return true;
			}
		}
		return $this->MyStatus()->CanCancel;
	}


	public function canDelete($member = null) {
		if(!$member) {$member = Member::currentMember();}
		if($member) {$memberID = $member->ID;} else {$memberID = 0;}
		$extended = $this->extendedCan('canDelete', $memberID);
		if($extended !== null) {return $extended;}
		return false;
	}


/*******************************************************
   * GET METHODS (e.g. Total, SubTotal, Title, etc...)
*******************************************************/

	function getTitle() {
		return $this->Title();
	}

	function Title() {
		if($this->ID) {
			$v = $this->i18n_singular_name(). " #$this->ID - ".$this->dbObject('Created')->format("D d M Y");
			if($this->MemberID && $this->Member()->exists() ) {
				if($this->MemberID != Member::currentUserID()) {
					$v .= " - ".$this->Member()->getName();
				}
			}
		}
		else {
			$v = _t("Order.NEW", "New")." ".$this->i18n_singular_name();
		}
		return $v;
	}

	/**
	 * Returns the subtotal of the modifiers for this order.
	 * If a modifier appears in the excludedModifiers array, it is not counted.
	 *
	 * @param $excluded string|array Class(es) of modifier(s) to ignore in the calculation.
	 * @todo figure out what the return type is? double? float?
	 */
	function ModifiersSubTotal($excluded = null, $onlyprevious = false) {
		$total = 0;
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if(is_array($excluded) && in_array($modifier->class, $excluded)) {
					if($onlyprevious) {
						break;
					}
					continue;
				}
				elseif($excluded && ($modifier->class == $excluded)) {
					if($onlyprevious) {
						break;
					}
					continue;
				}
				$total += $modifier->Total();
			}
		}
		return $total;
	}

	function ModifiersSubTotalAsCurrencyObject($excluded = null, $onlyprevious = false) {
		return DBField::create('Currency',$this->ModifiersSubTotal($excluded = null, $onlyprevious = false));
	}

	/**
	 * Returns the subtotal of the items for this order.
	 */
	function SubTotal() {
		$result = 0;
		if($items = $this->Items()) {
			foreach($items as $item) {
				if($item instanceOf OrderAttribute) {
					$result += $item->Total();
				}
			}
		}
		return $result;
	}

	function SubTotalAsCurrencyObject() {
		return DBField::create('Currency',$this->SubTotal());
	}
	/**
  	 * Returns the total cost of an order including the additional charges or deductions of its modifiers.
  	 */
	function Total() {
		return $this->SubTotal() + $this->ModifiersSubTotal();
	}

	function TotalAsCurrencyObject() {
		return DBField::create('Currency',$this->Total());
	}

	/**
	 * Checks to see if any payments have been made on this order
	 * and if so, subracts the payment amount from the order
	 * Precondition : The order is in DB
	 */
	function TotalOutstanding(){
		$total = $this->Total();
		$paid = $this->TotalPaid();
		$outstanding = $total - $paid;
		if(abs($outstanding) < self::get_maximum_ignorable_sales_payments_difference()) {
			return 0;
		}
		return $outstanding;
	}

	function TotalOutstandingAsCurrencyObject(){
		return DBField::create('Currency',$this->TotalOutstanding());
	}

	function TotalPaid() {
		$paid = 0;
		if($payments = $this->Payments()) {
			foreach($payments as $payment) {
				if($payment->Status == 'Success') {
					$paid += $payment->Amount;
				}
			}
		}
		return $paid;
	}

	function TotalPaidAsCurrencyObject(){
		return DBField::create('Currency',$this->TotalPaid());
	}

	function TotalItems() {
		$cart = self::current_order();
		if($cart) {
			if($cart = $this->Cart()) {
				if($orderItems = $cart->Items()) {
					return $orderItems->count();
				}
			}
		}
		return 0;
	}

	function TotalItemsTimesQuantity() {
		$cart = self::current_order();
		$qty = 0;
		if($cart) {
			if($cart = $this->Cart()) {
				if($orderItems = $cart->Items()) {
					foreach($orderItems as $item) {
						$qty += $item->Quantity;
					}
				}
			}
		}
		return $qty;
	}

	function Link() {
		return AccountPage::get_order_link($this->ID);
	}



	/**
	 * Return the currency of this order.
	 * Note: this is a fixed value across the entire site.
	 *
	 * @return string
	 */
	function Currency() {
		if(class_exists('Payment')) {
			return Payment::site_currency();
		}
	}

	// Order Template and ajax Management

	function TableSubTotalID() {
		return 'Table_Order_SubTotal';
	}

	function TableTotalID() {
		return 'Table_Order_Total';
	}

	function OrderForm_OrderForm_AmountID() {
		return 'OrderForm_OrderForm_Amount';
	}

	function CartSubTotalID() {
		return 'Cart_Order_SubTotal';
	}

	function CartTotalID() {
		return 'Cart_Order_Total';
	}

	function updateForAjax(array &$js) {
		$subTotal = $this->SubTotalAsCurrencyObject()->Nice();
		$total = $this->TotalAsCurrencyObject()->Nice();
		$js[] = array('id' => $this->TableSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->OrderForm_OrderForm_AmountID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
	}

	/**
	 * Return a link to the {@link CheckoutPage} instance
	 * that exists in the database.
	 *
	 * @return string
	 */
	 //TO DO: do we need this here??
	function checkoutLink() {
		return CheckoutPage::find_link();
	}

	/**
	 * Returns the correct shipping address. If there is an alternate
	 * shipping country then it uses that. Failing that, it returns
	 * the country of the member.
	 *
	 * @TODO This is pretty complicated code. It can be simplified.
	 *
	 * @param boolean $codeOnly If true, returns only the country code, instead
	 * 								of the full name.
	 * @return string
	 */
	function findShippingCountry($codeOnly = false) {
		if(!$this->ID) {
			$country = ShoppingCart::has_country() ? ShoppingCart::get_country() : EcommerceRole::find_country();
		}
		elseif(!$this->UseShippingAddress) {
			$country = EcommerceRole::find_country();
		}
		return $codeOnly ? $country : EcommerceRole::find_country_title($country);
	}

/*******************************************************
   * STANDARD SS METHODS (requireDefaultRecords, onBeforeDelete, etc...)
*******************************************************/

	/**
	 * Updates the database structure of the Order table
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();

		// 1) If some orders with the old structure exist (hasShippingCost, Shipping and AddedTax columns presents in Order table), create the Order Modifiers SimpleShippingModifier and TaxModifier and associate them to the order

		// we must check for individual database types here because each deals with schema in a none standard way
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $exist = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='Order' AND column_name = 'Shipping'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $exist = DB::query("SHOW COLUMNS FROM \"Order\" LIKE 'Shipping'")->numRecords();
		}
 		if($exist > 0) {
 			if($orders = DataObject::get('Order')) {
 				foreach($orders as $order) {
 					$id = $order->ID;
 					$hasShippingCost = DB::query("SELECT \"hasShippingCost\" FROM \"Order\" WHERE \"ID\" = '$id'")->value();
 					$shipping = DB::query("SELECT \"Shipping\" FROM \"Order\" WHERE \"ID\" = '$id'")->value();
 					$addedTax = DB::query("SELECT \"AddedTax\" FROM \"Order\" WHERE \"ID\" = '$id'")->value();
					$country = $order->findShippingCountry(true);
 					if($hasShippingCost == '1' && $shipping != null) {
 						$modifier1 = new SimpleShippingModifier();
 						$modifier1->Amount = $shipping < 0 ? abs($shipping) : $shipping;
 						$modifier1->Type = 'Chargable';
 						$modifier1->OrderID = $id;
 						$modifier1->Country = $country;
 						$modifier1->ShippingChargeType = 'Default';
 						$modifier1->write();
 					}
 					if($addedTax != null) {
 						$modifier2 = new TaxModifier();
 						$modifier2->Amount = $addedTax < 0 ? abs($addedTax) : $addedTax;
 						$modifier2->Type = 'Chargable';
 						$modifier2->OrderID = $id;
 						$modifier2->Country = $country;
 						$modifier2->Name = 'Undefined After Ecommerce Upgrade';
 						$modifier2->TaxType = 'Exclusive';
 						$modifier2->write();
 					}
 				}
 				DB::alteration_message('The \'SimpleShippingModifier\' and \'TaxModifier\' objects have been successfully created and linked to the appropriate orders present in the \'Order\' table', 'created');
 			}
 			DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"hasShippingCost\" \"_obsolete_hasShippingCost\" tinyint(1)");
 			DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"Shipping\" \"_obsolete_Shipping\" decimal(9,2)");
 			DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"AddedTax\" \"_obsolete_AddedTax\" decimal(9,2)");
 			DB::alteration_message('The columns \'hasShippingCost\', \'Shipping\' and \'AddedTax\' of the table \'Order\' have been renamed successfully. Also, the columns have been renamed respectly to \'_obsolete_hasShippingCost\', \'_obsolete_Shipping\' and \'_obsolete_AddedTax\'', 'obsolete');
		}


		//set starting order number ID
		$number = intval(Order::get_order_id_start_number());
		$currentMax = 0;
		//set order ID
		if($number) {
			$count = DB::query("SELECT COUNT( \"ID\" ) FROM \"Order\" ")->value();
		 	if($count > 0) {
				$currentMax = DB::Query("SELECT MAX( \"ID\" ) FROM \"Order\"")->value();
			}
			if($number > $currentMax) {
				DB::query("ALTER TABLE \"Order\"  AUTO_INCREMENT = $number ROW_FORMAT = DYNAMIC ");
				DB::alteration_message("Change OrderID start number to ".$number, "edited");
			}
		}
		//fix bad status
		$dos = self::get_order_status_options();
		if($dos) {
			$firstOption = $dos->First();
			$badOrders = DataObject::get("Order", "\"StatusID\" = '' OR \"StatusID\" = 0 OR \"StatusID\" IS NULL");
			if($badOrders && $firstOption) {
				foreach($badOrders as $order) {
					$order->StatusID = $firstOption->ID;
					$order->write();
					DB::alteration_message("No order status for order number #".$order->ID." reverting to: $firstOption->Name.","error");
				}
			}
		}
		//move to ShippingAddress
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $shippingFieldsExists = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='Order' AND column_name = 'ShippingAddress'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $shippingFieldsExists = DB::query("SHOW COLUMNS FROM \"Order\" LIKE 'ShippingAddress'")->numRecords();
		}
		if($shippingFieldsExists) {
 			if($orders = DataObject::get('Order', "\"UseShippingAddress\" = 1  OR (\"ShippingName\" IS NOT NULL AND \"ShippingName\" <> '')")) {
 				foreach($orders as $order) {
					$obj = new ShippingAddress();
					if(isset($order->ShippingName)) {$obj->ShippingName = $order->ShippingName;}
					if(isset($order->ShippingAddress)) {$obj->ShippingAddress = $order->ShippingAddress;}
					if(isset($order->ShippingAddress2)) {$obj->ShippingAddress2 = $order->ShippingAddress2;}
					if(isset($order->ShippingCity)) {$obj->ShippingCity = $order->ShippingCity;}
					if(isset($order->ShippingPostalCode)) {$obj->ShippingPostalCode = $order->ShippingPostalCode;}
					if(isset($order->ShippingState)) {$obj->ShippingState = $order->ShippingState;}
					if(isset($order->ShippingCountry)) {$obj->ShippingCountry = $order->ShippingCountry;}
					if(isset($order->ShippingPhone)) {$obj->ShippingPhone = $order->ShippingPhone;}
					$obj->OrderID = $order->ID;
					$obj->write();
					$order->ShippingAddressID = $obj->ID;
					$order->write();
				}
			}
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingName\" \"_obsolete_ShippingName\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingAddress\" \"_obsolete_ShippingAddress\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingAddress2\" \"_obsolete_ShippingAddress2\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingCity\" \"_obsolete_ShippingCity\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingPostalCode\" \"_obsolete_ShippingPostalCode\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingState\" \"_obsolete_ShippingState\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingCountry\" \"_obsolete_ShippingCountry\" Varchar(255)");
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingPhone\" \"_obsolete_ShippingPhone\" Varchar(255)");
		}
		//move to ShippingAddress
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $statusFieldExists = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='Order' AND column_name = 'Status'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $statusFieldExists = DB::query("SHOW COLUMNS FROM \"Order\" LIKE 'Status'")->numRecords();
		}
		if($statusFieldExists) {
		// 2) Cancel status update
			$orders = DataObject::get('Order', "\"Status\" = 'Cancelled'");
			$admin = Member::currentMember();
			if($orders && $admin) {
				foreach($orders as $order) {
					$order->CancelledByID = $admin->ID;
					$order->write();
				}
				DB::alteration_message('The orders which status was \'Cancelled\' have been successfully changed to the status \'AdminCancelled\'', 'changed');
			}
			$rows = DB::query("SELECT \"ID\", \"Status\" FROM \"Order\"");
			if($rows) {
				$CartObject = null;
				$UnpaidObject = null;
				$PaidObject = null;
				$SentObject = null;
				$AdminCancelledObject = null;
				$MemberCancelledObject = null;
 				foreach($rows as $row) {
					switch($row["Status"]) {
						case "Cart":
							if(!$CartObject) {
								if(!($CartObject = DataObject::get_one("OrderStep", "\"Code\" = 'CREATED'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($CartObject = DataObject::get_one("OrderStep", "\"Code\" = 'CREATED'")) {
								DB::query("UPDATE \"Order\" SET StatusID = ".$CartObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}
							break;
						case "Query":
						case "Unpaid":
							if(!$UnpaidObject) {
								if(!($UnpaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'SUBMITTED'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($UnpaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'SUBMITTED'")) {
								DB::query("UPDATE \"Order\" SET StatusID = ".$UnpaidObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}

							break;
						case "Processing":
						case "Paid":
							if(!$PaidObject) {
								if(!($PaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'PAID'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($PaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'PAID'")) {
								DB::query("UPDATE \"Order\" SET StatusID = ".$PaidObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}
							break;
						case "Sent":
						case "Complete":
							if(!$PaidObject) {
								if(!($SentObject = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($SentObject = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'")) {
								DB::query("UPDATE \"Order\" SET StatusID = ".$SentObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}
							break;
						case "AdminCancelled":
							if(!$AdminCancelledObject) {
								if(!($AdminCancelledObject  = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if(!$adminID) {
								$adminID = Member::currentUserID();
								if(!$adminID) {
									$adminID = 1;
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$AdminCancelledObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"].", \"CancelledByID\" = ".$adminID);
							break;
						case "MemberCancelled":
							if(!$MemberCancelledObject) {
								if(!($MemberCancelledObject = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$MemberCancelledObject->ID.", \"CancelledByID\" = \"MemberID\" WHERE \"Order\".\"ID\" = '".$row["ID"]."'");
							break;
					}
				}
			}
 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"Status\" \"_obsolete_Status\" Varchar(255)");
		}
	}


	function populateDefaults() {
		parent::populateDefaults();
		//@Session::start();
		//$this->SessionID = Session_id();
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->CancelledByID) {
			$this->CancelledByID = 0;
		}
	}

	function onAfterWrite() {
		parent::onAfterWrite();
	}

	/**
	 * delete attributes, statuslogs, and payments
	 */
	 //TODO: make this optional??
	function onBeforeDelete(){
		if($attributes = $this->Attributes()){
			foreach($attributes as $attribute){
				//TODO: not working yet - Order Items are still found in DB
				$attribute->delete();
				$attribute->destroy();
			}
		}

		if($statuslogs = $this->OrderStatusLogs()){
			foreach($statuslogs as $log){
				$log->delete();
				$log->destroy();
			}
		}

		if($payments = $this->Payments()){
			foreach($payments as $payment){
				$payment->delete();
				$payment->destroy();
			}
		}
		if($shippingAddress = $this->ShippingAddress()) {
			$shippingAddress->delete();
			$shippingAddress-->destroy();
		}
		parent::onBeforeDelete();

	}

	function debug(){

		$val = "<h3>Database record: $this->class</h3>\n<ul>\n";
		if($this->record) foreach($this->record as $fieldName => $fieldVal) {
			$val .= "\t<li>$fieldName: " . Debug::text($fieldVal) . "</li>\n";
		}
		$val .= "</ul>\n";
		$val .= "<h4>Items</h4>";
		if($this->Items())
			$val .= $this->Items()->debug();
		$val .= "<h4>Modifiers</h4>";
		if($this->Modifiers())
			$val .= $this->Modifiers()->debug();

		return $val;
	}

	public static function set_email($email) {
		user_error("this static method has been replaced by the siteconfig", E_USER_ERROR);
	}

	public static function set_subject($subject) {
		user_error("this static method is now part of the siteconfig", E_USER_ERROR);
	}

}



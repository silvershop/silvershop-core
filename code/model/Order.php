<?php

/**
 * @description: The order class is a databound object for handling Orders within SilverStripe.
 * Note that it works closely with the ShoppingCart class, which accompanies the Order
 * until it has been paid for / confirmed by the user.
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 *
 * @package: ecommerce
 * @sub-package: model
 *
 **/

class Order extends DataObject {

	public static $db = array(
		'SessionID' => "Varchar(32)", //so that in the future we can link sessions with Orders.... One session can have several orders, but an order can onnly have one session
		'UseShippingAddress' => 'Boolean',
		'CustomerOrderNote' => 'Text'
	);

	public static $has_one = array(
		'Member' => 'Member',
		'BillingAddress' => 'BillingAddress',
		'ShippingAdress' => 'ShippingAdress',
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
		'IsCancelled' => 'Boolean',
		'Country' => "Varchar", //This is the applicable country for the order - for tax purposes, etc....
		'FullNameCountry' => "Varchar"
	);

	public static $create_table_options = array(
		'MySQLDatabase' => 'ENGINE=InnoDB'
	);

	public static $singular_name = "Order";
		function i18n_singular_name() { return _t("Order.ORDER", "Order");}

	public static $plural_name = "Orders";
		function i18n_plural_name() { return _t("Order.ORDERS", "Orders");}

	/**
	 * Modifiers represent the additional charges or
	 * deductions associated to an order, such as
	 * shipping, taxes, vouchers etc.
	 *
	 * @var array
	 */
	protected static $modifiers = array();

	/**
	 * Total Items : total items in cart
	 *
	 * @var integer / null
	 */

	protected static $total_items = null;

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


	public static function get_modifier_forms($controller) {
		user_error("this method has been changed to getModifierForms, the current function has been depreciated", E_USER_ERROR);
	}

	/**
	 * Returns a set of modifier forms for use in the checkout order form,
	 * Controller is optional, because the orderForm has its own default controller.
	 *
	 *@return DataObjectSet of OrderModiferForms
	 **/
	public function getModifierForms($optionalController = null) {
		$dos = new DataObjectSet();
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if($modifier->showForm()) {
					if($form = $modifier->getModifierForm($optionalController)) {
						$dos->push($form);
					}
				}
			}
		}
		if( $dos->count() ) {
			return $dos;
		}
		else {
			return null;
		}
	}


	/**
	 * The maximum difference between the total cost of the order and the total payment made.
	 * If this value is, for example, 10 cents and the total amount outstanding for an order is less than
	 * ten cents, than the order is considered "paid".
	 *@var Float
	 **/
	protected static $maximum_ignorable_sales_payments_difference = 0.01;
		static function set_maximum_ignorable_sales_payments_difference(float $f) {self::$maximum_ignorable_sales_payments_difference = $f;}
		static function get_maximum_ignorable_sales_payments_difference() {return(float)self::$maximum_ignorable_sales_payments_difference;}

	/**
	 * Each order has an order number.  Normally, the order numbers start at one,
	 * but in case you would like this number to be different you can set it here.
	 *
	 *@var Integer
	 **/
 	protected static $order_id_start_number = 0;
		static function set_order_id_start_number(integer $i) {self::$order_id_start_number = $i;}
		static function get_order_id_start_number() {return(integer)self::$order_id_start_number;}


	/**
	 * This function returns the OrderSteps
	 *
	 *@returns: DataObjectSet (OrderSteps)
	 **/
	public static function get_order_status_options() {
		return DataObject::get("OrderStep");
	}

	/**
	 * Like the standard get_by_id, but it checks whether we are allowed to view the order.
	 *
	 *@returns: DataObject (Order)
	 **/
	public static function get_by_id_if_can_view($id) {
		$obj = DataObject::get_by_id("Order", $id);
                if(is_object($obj)){
                    if($obj->canView()) {
                            return $obj;
                    }
                }
		return null;
	}

	/**
	 * Like the standard get_by_id, but it checks if we are allowed to view it!
	 *@return DataObject (Order)
	 **/
	public static function get_by_id_and_member_id($id, $memberID) {
		$obj = Order::get_by_id_if_can_view($id);
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
	 * STANDARD SILVERSTRIPE STUFF
	 **/
	public static $summary_fields = array(
		"\"Order\".\"ID\"" => "ID",
		'Member.Surname',
		'Member.Email',
		'TotalAsCurrencyObject.Nice' => 'Total',
		'Status.Name',
	);
		public static function get_summary_fields() {return self::$summary_fields;}

	/**
	 * STANDARD SILVERSTRIPE STUFF
	 **/
	public static $searchable_fields = array(
		'ID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
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
			'title' => "Date (e.g. Today)"
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

	/**
	 * STANDARD SILVERSTRIPE STUFF
	 **/
	function scaffoldSearchFields(){
		$fieldSet = parent::scaffoldSearchFields();
		if($statusOptions = self::get_order_status_options()) {
			$fieldSet->push(new CheckboxSetField("StatusID", "Status", $statusOptions->toDropDownMap()));
		}
		$fieldSet->push(new DropdownField("TotalPaid", "Has Payment", array(-1 => "(Any)", 1 => "yes", 0 => "no")));
		$fieldSet->push(new DropdownField("CancelledByID", "Cancelled", array(-1 => "(Any)", 1 => "yes", 0 => "no")));
		return $fieldSet;
	}

	/**
	 * STANDARD SILVERSTRIPE STUFF
	 **/
	function validate() {
		if($this->StatusID) {
			//do nothing
		}
		else {
			$firstStep = DataObject::get_one("OrderStep");
			if($firstStep) {
				$this->StatusID = $firstStep->ID;
				if($this->StatusID) {
					//rerun with valid StatusID in place
					return $this->validate();
				}
			}
			return new ValidationResult(false, _t("Order.MUSTSETSTATUS", "You must set a status"));
		}
		return parent::validate();
	}

	/**
	 * STANDARD SILVERSTRIPE STUFF
	 * broken up into readOnly and editable
	 **/
	function getCMSFields(){
		$this->tryToFinaliseOrder();
		$fields = parent::getCMSFields();
		$readOnly = (bool)!$this->MyStep()->CustomerCanEdit;
		$fieldsAndTabsToBeRemoved = array('MemberID', 'Attributes', 'SessionID', 'BillingAddressID', 'ShippingAddressID', 'UseShippingAddress', 'OrderStatusLogs', 'Payments');
		if(!$readOnly) {
			$fieldsAndTabsToBeRemoved[] = "Emails";
		}
		else {
			$fieldsAndTabsToBeRemoved[] = "CustomerOrderNote";
		}
		foreach($fieldsAndTabsToBeRemoved as $field) {
			$fields->removeByName($field);
		}
		$fields->insertBefore(new LiteralField('Title',"<h2>".$this->Title()."</h2>"),'Root');
		if($readOnly) {
			$htmlSummary = $this->renderWith("Order");
			$printlabel = _t("Order.PRINTINVOICE", "Print Invoice");
			$fields->addFieldsToTab('Root.Main', array(
				new LiteralField("PrintInvoice",'<p class="print"><a href="OrderReport_Popup/index/'.$this->ID.'?print=1" onclick="javascript: window.open(this.href, \'print_order\', \'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50\'); return false;">'.$printlabel.'</a></p>')
			));
			$fields->addFieldToTab('Root.Main', new LiteralField('MainDetails', $htmlSummary));
			$paymentsTable = new HasManyComplexTableField(
				$this,
				"Payments", //$name
				"Payment", //$sourceClass =
				null, //$fieldList =
				null, //$detailedFormFields =
				"\"OrderID\" = ".$this->ID."", //$sourceFilter =
				"\"Created\" ASC", //$sourceSort =
				null //$sourceJoin =
			);
			$paymentsTable->setPageSize(100);
			if($this->IsPaid()){
				$paymentsTable->setPermissions(array('export', 'show'));
			}
			else {
				$paymentsTable->setPermissions(array('edit', 'delete', 'export', 'add', 'show'));
			}
			$paymentsTable->setShowPagination(false);
			$paymentsTable->setRelationAutoSetting(true);
			$fields->addFieldToTab('Root.Payments',$paymentsTable);
			if($member = $this->Member()) {
				$fields->addFieldToTab('Root.Customer', $member->getEcommerceFieldsForCMS());
			}
			/*
			$fields->addFieldsToTab(
				"Root.Delivery",
				array(
					new CheckboxField("UseShippingAddress", "Shipping Address is not the same as Billing Address"),
					new HeaderField("DispatchLog", _t("Order.DISPATCHLOG", "Dispatch Log")),
					new ComplexTableField($controller = "OrderStatusLog_Dispatch", "OrderStatusLog_Dispatch", "OrderStatusLog_Dispatch", $fieldList = null, $detailFormFields = null, $sourceFilter = "\"OrderID\" = ".$this->ID, $sourceSort = "", $sourceJoin = "")
				)
			);
			*/
		}
		else {
			$fields->addFieldToTab('Root.Main', new LiteralField('MainDetails', _t("Order.NODETAILSSHOWN", '<p>No details are shown here as this order has not been submitted yet. Once you change the status of the order more options will be available.</p>')));
			$orderItemsTable = new HasManyComplexTableField(
				$this, //$controller
				"Attributes", //$name =
				"OrderItem", //$sourceClass =
				null, //$fieldList =
				null, //$detailedFormFields =
				"\"OrderID\" = ".$this->ID."", //$sourceFilter =
				"\"Created\" ASC", //$sourceSort =
				null //$sourceJoin =
			);
			$orderItemsTable->setPermissions(array('edit', 'delete', 'export', 'add', 'inlineadd', "show"));
			$orderItemsTable->setShowPagination(false);
			$orderItemsTable->setRelationAutoSetting(true);
			$orderItemsTable->addSummary(
				"Total",
				array("Total" => array("sum","Currency->Nice"))
			);
			$fields->addFieldToTab('Root.Items',$orderItemsTable);
			$modifierTable = new TableListField(
				"OrderModifiers", //$name
				"OrderModifier", //$sourceClass =
				OrderModifier::$summary_fields, //$fieldList =
				"\"OrderID\" = ".$this->ID."" //$sourceFilter =
			);
			$modifierTable->setPermissions(array('edit', 'delete', 'export', 'add', 'show'));
			$modifierTable->setPageSize(100);
			$fields->addFieldToTab('Root.Extras',$modifierTable);
		}
		if($this->MyStep()) {
			$this->MyStep()->addOrderStepFields($fields, $this);
		}
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}

	/**
	 *
	 *@return HasManyComplexTableField
	 **/
	function OrderStatusLogsTable($sourceClass) {
		$orderStatusLogsTable = new HasManyComplexTableField(
			$this,
			"OrderStatusLogs", //$name
			$sourceClass, //$sourceClass =
			null, //$fieldList =
			null, //$detailedFormFields =
			"\"OrderID\" = ".$this->ID.""
		);
		$orderStatusLogsTable->setPageSize(100);
		$orderStatusLogsTable->setShowPagination(false);
		$orderStatusLogsTable->setRelationAutoSetting(true);
		return $orderStatusLogsTable;
	}



/*******************************************************
   * MAIN TRANSITION FUNCTIONS:
*******************************************************/

	/**
	 *init runs on start of a new Order (@see onAfterWrite)
	 * it adds all the modifiers to the orders and the starting OrderStep
	 *
	 * @return DataObject (Order)
	 **/

	public function init() {
		//to do: check if shop is open....
		if(!$this->StatusID) {
			if($newStatus = DataObject::get_one("OrderStep")) {
				$this->StatusID = $newStatus->ID;
			}
			else {
				//user_error("There are no OrderSteps ... please Run Dev/Build", E_USER_WARNING);
			}
		}
		$createdModifiersClassNames = array();
		$this->modifiers = $this->modifiersFromDatabase($includingRemoved = true);
		if($this->modifiers) {
			foreach($this->modifiers as $modifier) {
				$createdModifiersClassNames[$modifier->ID] = $modifier->ClassName;
			}
		}
		else {
			$this->modifiers = new DataObjectSet();
		}
		if(is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $numericKey => $className) {
				if(!in_array($className, $createdModifiersClassNames)) {
					if(class_exists($className)) {
						$modifier = new $className();
						//only add the ones that should be added automatically
						if(!$modifier->DoNotAddAutomatically()) {
							if($modifier instanceof OrderModifier) {
								$modifier->OrderID = $this->ID;
								$modifier->Sort = $numericKey;
								//init method includes a WRITE
								$modifier->init();
								//IMPORTANT - add as has_many relationship  (Attributes can be a modifier OR an OrderItem)
								$this->Attributes()->add($modifier);
								$this->modifiers->push($modifier);
							}
						}
					}
					else{
						user_error("reference to a non-existing class: ".$className." in modifiers", E_USER_NOTICE);
					}
				}
			}
		}
		$this->extend('onInit', $this);
		$this->write();
		return $this;
	}

	/**
	 * Sets up the Member, Billing and Shipping Address in the order
	 * so that everything is at pre-populated as can be in the checkout.
	 * TO DO: consider if this should go into an OrderStep???
	 * NOTE: this is not used at the moment...
	 **/
	public function prepareForCheckout() {
		$this->CreateMember();
		$this->CreateAddress("BillingAddress", "BillingAddress");
		if(OrderAddress::get_use_separate_shipping_address()) {
			$this->CreateAddress("ShippingAddress", "ShippingAddress");
		}
	}


	/**
	 * Goes through the order steps and tries to "apply" the next status to the order
	 *
	 **/
	public function tryToFinaliseOrder() {
		do {
			//status of order is being progressed
			$nextStatusID = $this->doNextStatus();
		}
		while ($nextStatusID);
	}

	/**
	 * Goes through the order steps and tries to "apply" the next
	 *@return Integer (StatusID or false if the next status can not be "applied")
	 **/
	public function doNextStatus() {
		if($this->MyStep()->initStep($this)) {
			if($this->MyStep()->doStep($this)) {
				if($nextOrderStepObject = $this->MyStep()->nextStep($this)) {
					$this->StatusID = $nextOrderStepObject->ID;
					$this->write();
					return $this->StatusID;
				}
			}
		}
		return false;
	}




/*******************************************************
   * STATUS RELATED FUNCTIONS / SHORTCUTS
*******************************************************/


	/**
	 * @return DataObject (current OrderStep)
	 */
	public function MyStep() {
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
	 * @return DataObject (current OrderStep that can be seen by customer)
	 */
	public function CurrentStepVisibleToCustomer() {
		$obj = $this->MyStep();
		if($obj->HideStepFromCustomer) {
			$obj = DataObject::get_one("OrderStep", "\"Sort\" < ".$obj->Sort." AND \"HideStepFromCustomer\" = 0");
			if(!$obj) {
				$obj = DataObject::get_one("OrderStep");
			}
		}
		return $obj;
	}

	/**
	 * works out if the order is still at the first OrderStep.
	 * @return boolean
	 */
	public function IsFirstStep() {
		$firstStep = DataObject::get_one("OrderStep");
		$currentStep = $this->MyStep();
		if($firstStep && $currentStep) {
			if($firstStep->ID == $currentStep->ID) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Is the order still being "edited" by the customer?
	 * @return boolean
	 */
	function IsInCart(){
		return (bool)$this->canEdit();
	}

	/**
	 * The order has "passed" the IsInCart phase
	 * @return boolean
	 */
	function IsPastCart(){
		return !(bool)$this->IsInCart();
	}

	/**
	* Are there still steps the order needs to go through?
	 * @return boolean
	 */
	function IsUncomplete() {
		return (bool)$this->MyStep()->ShowAsUncompletedOrder;
	}

	/**
	* Is the order in the :"processing" phaase.?
	 * @return boolean
	 */
	function IsProcessing() {
		return (bool)$this->MyStep()->ShowAsInProcessOrder;
	}

	/**
	* Is the order completed?
	 * @return boolean
	 */
	function IsCompleted() {
		return (bool)$this->MyStep()->ShowAsCompletedOrder;
	}

	/**
	 * Has the order been paid?
	 * @return boolean
	 */
	function IsPaid() {
		return (bool)($this->Total() > 0 && $this->TotalOutstanding() <= 0);
	}

	/**
	 * Has the order been cancelled?
	 * @return boolean
	 */
	public function IsCancelled() {
		return (bool)$this->CancelledByID;
	}

	/**
	 * Has the order been cancelled by the customer?
	 * @return boolean
	 */
	function IsCustomerCancelled() {
		if($this->MemberID == $this->IsCancelledID && $this->MemberID > 0) {
			return true;
		}
		return false;
	}


	/**
	 * Has the order been cancelled by the  administrator?
	 * @return boolean
	 */
	function IsAdminCancelled() {
		if($this->IsCancelled()) {
			if(!$this->IsCustomerCancelled()) {
				$admin = DataObject::get_by_id("Member", $this->CancelledByID);
				if($admin) {
					if($admin->IsShopAdmin()) {
						return true;
					}
				}
			}
		}
		return false;
	}


	/**
	* Is the Shop Closed for business?
	 * @return boolean
	 */
	function ShopClosed() {
		$siteConfig = DataObject::get_one("SiteConfig");
		return $siteConfig->ShopClosed;
	}




/*******************************************************
   * LINKING ORDER WITH MEMBER AND ADDRESS
*******************************************************/


	/**
	 * returns a member linked to the order -
	 * this member is NOT written, if a member is already linked, it will return the existing member.
	 *@return: DataObject (Member)
	 **/
	public function CreateMember() {
		if(!$this->MemberID) {
			if($member = Member::currentMember()) {
				$this->MemberID = $member->ID;
				$this->write();
			}
			else {
				$member = new Member();
			}
		}
		else {
			$member = DataObject::get_by_id("Member", $this->MemberID);
		}
		return $member;
	}

	/**
	 * DOES NOT WRITE OrderAddress for the sake of it.
	 * returns either the existing one or a new one...
	 * Method used to retrieve object e.g. for $order->BillingAddress(); "BillingAddress" is the method name you can use.
	 * If the method name is the same as the class name then dont worry about providing one.
	 *
	 *@param String $className   - ClassName of the Address (e.g. BillingAddress or ShippingAddress)
	 *@param String $alternativeMethodName  -
	 *
	 * @return DataObject (OrderAddress)
	 **/

	public function CreateAddress(string $className, $alternativeMethodName = '') {
		$variableName = $className."ID";
		if($alternativeMethodName) {
			$methodName = $alternativeMethodName;
		}
		else {
			$methodName = $className;
		}
		$address = null;
		if($this->$variableName) {
			if($address = $this->$methodName()) {
				if($address->OrderID != $this->ID && $this->ID) {
					$address->OrderID = $this->ID;
					$address->write();
				}
			}
		}
		if(!$address) {
			$address = new $className();
			$address->OrderID = $this->ID;
			if($member = $this->Member()) {
				$address->CopyLastaddressFromMember($member, false);
			}
		}
		return $address;
	}

	/**
	 * Sets the country in the billing and shipping address
	 *
	 **/

	public function SetCountry($countryCode) {
		if($billingAddress = $this->CreateAddress("BillingAddress", "BillingAddress")) {
			$billingAddress->SetCountry($countryCode);
		}
		if(OrderAddress::get_use_separate_shipping_address()) {
			if($shippingAddress = $this->CreateAddress("ShippingAddress", "ShippingAddress")) {
				$shippingAddress->SetCountry($countryCode);
			}
		}
	}


/*******************************************************
   * CUSTOMER COMMUNICATION AND ADDRESS STUFF....
*******************************************************/

	/**
	 * Send the invoice of the order by email.
	 */
	function sendInvoice($message = "", $resend = false) {
		$subject = str_replace("{OrderNumber}", $this->ID,Order_Email::get_subject());
		$replacementArray = array("Message" => $message);
		return $this->sendEmail('Order_ReceiptEmail', $subject, $replacementArray, $resend);
	}

	/**
	 * Send the receipt of the order by email.
	 * Precondition: The order payment has been successful
	 */
	function sendReceipt($message = "", $resend = false) {
		$subject = str_replace("{OrderNumber}", $this->ID,Order_Email::get_subject());
		$replacementArray = array(
			'Message' => $message
		);
		return $this->sendEmail('Order_ReceiptEmail', $subject, $replacementArray, $resend);
	}

	/**
	 * Send a message to the client containing the latest
	 * note of {@link OrderStatusLog} and the current status.
	 *
	 * Used in {@link OrderReport}.
	 *
	 * @param string $note Optional note-content (instead of using the OrderStatusLog)
	 */
	function sendStatusChange($subject, $message = '', $resend = false) {
		if(!$message) {
			$emailableLogs = DataObject::get('OrderStatusLog', "\"OrderID\" = {$this->ID} AND \"EmailCustomer\" = 1 AND \"EmailSent\" = 0 ", "\"Created\" DESC", null, 1);
			if($logs) {
				$latestEmailableLog = $lemailableLogs->First();
				$message = $latestEmailableLog->Note;
			}
		}
		if(!$subject) {
			$subject = str_replace("{OrderNumber}", $this->ID,Order_Email::get_subject());
		}
		$replacementArray = array("Message" => $message);
		return $this->sendEmail('Order_StatusEmail', $subject, $replacementArray, $resend);
	}


	/**
	 * Send a mail of the order to the client (and another to the admin).
	 *
	 * @param String $emailClass - the class name of the email you wish to send
	 * @param String $subject - email subject
	 * @param Array $replacementArray - array of fields to replace with data...
	 * @param Boolean $copyToAdmin - true by default, whether it should send a copy to the admin
	 *
	 * @return Boolean TRUE for success, FALSE for failure (not tested)
	 */
	protected function sendEmail($emailClass, $subject, $replacementArray = array(), $resend = false) {
		$replacementArray["Order"] = $this;
		$replacementArray["EmailLogo"] = SiteConfig::current_site_config()->EmailLogo();
 		$from = Order_Email::get_from_email();
 		$to = $this->Member()->Email;
		//TO DO: should be a payment specific message as well???
		$email = new $emailClass();
		if(!($email instanceOf Email)) {
			user_error("No correct email class provided.", E_USER_ERROR);
		}
 		$email->setFrom($from);
 		$email->setTo($to);
 		$email->setSubject($subject);
		$email->populateTemplate($replacementArray);
		return $email->send(null, $this, $resend);
	}




/*******************************************************
   * ITEM MANAGEMENT
*******************************************************/

	/**
	 * Returns the items of the order.
	 * Items are the order items (products) and NOT the modifiers (discount, tax, etc...)
	 *
	 *@param String filter - where statement to exclude certain items.
	 *
	 *@return DataObjectSet (OrderItems)
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
	 *@param String filter - where statement to exclude certain items.
	 *
	 * @return DataObjectSet
	 */
	protected function itemsFromDatabase($filter = null) {
		$extrafilter = ($filter) ? " AND $filter" : "";
		$items = DataObject::get("OrderItem", "\"OrderID\" = '$this->ID' AND \"Quantity\" > 0 $extrafilter");
		return $items;
	}


	/**
	 * Returns the modifiers of the order, if it hasn't been saved yet
	 * it returns the modifiers from session, if it has, it returns them
	 * from the DB entry. ONLY USE OUTSIDE ORDER
	 *
	 *@param String filter - where statement to exclude certain items.
	 *
	 *@return DataObjectSet(OrderModifiers)
	 */
 	function Modifiers($filter = '') {
		return $this->modifiersFromDatabase();
	}

	/**
	 * Get all {@link OrderModifier} instances that are
	 * available as records in the database.
	 * NOTE: includes REMOVED Modifiers, so that they do not get added again...
	 *
	 *@param String filter - where statement to exclude certain items.
	 *
	 * @return DataObjectSet
	 */
	protected function modifiersFromDatabase($filter = '') {
		$extrafilter = ($filter) ? " AND $filter" : "";
		return DataObject::get('OrderModifier', "\"OrderAttribute\".\"OrderID\" = ".$this->ID." $extrafilter");
	}

	/**
	 * Calculates and updates all the modifiers.
	 **/

	public function calculateModifiers($force = false) {
		//check if order has modifiers already
		//check /re-add all non-removable ones
		//$start = microtime();
		$createdModifiers = $this->modifiersFromDatabase();
		if($createdModifiers) {
			foreach($createdModifiers as $modifier){
				if($modifier) {
					$modifier->runUpdate();
				}
			}
		}
		$this->extend("onCalculate");
	}

	/**
	 * @param String $className: class name for the modifier
	 * @return DataObject (OrderModifier)
	 **/
	function RetrieveModifier(String $className) {
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if($modifier instanceof $className) {
					return $modifier;
				}
			}
		}
	}

	/**
	 * Returns a TaxModifier object that provides
	 * information about tax on this order.
	 * @return DataObject (TaxModifier)
	 */
	function TaxInfo() {
		return $this->RetrieveModifier("TaxModifier");
	}


/*******************************************************
   * LOGS
*******************************************************/

	/**
	 * Returns all the order logs that the current member can view
	 * i.e. some order logs can only be viewed by the admin (e.g. suspected fraud orderlog).
	 *
	 * @return DataObjectSet|Null (set of OrderLogs)
	 **/

	function CanViewOrderStatusLogs() {
		$canViewOrderStatusLogs = new DataObjectSet();
		$logs = $this->OrderStatusLogs();
		foreach($logs as $log) {
			if($log->canView()) {
				$canViewOrderStatusLogs->push($log);
			}
		}
		if($canViewOrderStatusLogs->count()) {
			return $canViewOrderStatusLogs;
		}
		return null;
	}



/*******************************************************
   * CRUD METHODS (e.g. canView, canEdit, canDelete, etc...)
*******************************************************/

	/**
	 *
	 * @return DataObject (Member)
	 **/
	protected function getMemberForCanFunctions($member = null) {
		if(!$member) {$member = Member::currentMember();}
		if(!$member) {
			$member = new Member();
			$member->ID = 0;
		}
		return $member;
	}


	/**
	 *
	 *@return Boolean
	 **/
	public function canCreate($member = null) {
		$member = $this->getMemberForCanFunctions($member);
		$extended = $this->extendedCan('canCreate', $member->ID);
		if($extended !== null) {return $extended;}
		//TO DO: setup a special group of shop admins (probably can copy some code from Blog)
		if($member->ID) {
			return $member->IsShopAdmin();
		}
	}

	/**
	 *
	 *@return Boolean
	 **/
	public function canView($member = null) {
		$member = $this->getMemberForCanFunctions($member);
		//check if this has been "altered" in a DataObjectDecorator
		$extended = $this->extendedCan('canView', $member->ID);
		if($extended !== null) {return $extended;}
		//no member present: ONLY if the member can edit the order it can be viewed...
		if(!$this->MemberID) {
			//return $this->canEdit($member);
		}
		elseif($member->ID) {
			if($member->IsShopAdmin()) {
				return true;
			}
			if($this->MemberID == $member->ID) {
				if(!$this->IsCancelled()) {
					return true;
				}
			}
		}
		return false;
	}


	/**
	 *
	 *@return Boolean
	 **/
	function canEdit($member = null) {
		$member = $this->getMemberForCanFunctions($member);
		$extended = $this->extendedCan('canEdit', $member->ID);
		if($extended !== null) {return $extended;}
		if(!$this->canView($member) || $this->IsCancelled()) {
			return false;
		}
		if($member->ID) {
			if($member->IsShopAdmin()) {
				return true;
			}
		}
		return $this->MyStep()->CustomerCanEdit;
	}

	/**
	 *
	 *@return Boolean
	 **/
	function canPay($member = null) {
		$member = $this->getMemberForCanFunctions($member);
		$extended = $this->extendedCan('canPay', $member->ID);
		if($extended !== null) {return $extended;}
		if($this->IsPaid() || $this->IsCancelled()) {
			return false;
		}
		return $this->MyStep()->CustomerCanPay;
	}

	/**
	 *
	 *@return Boolean
	 **/
	function canCancel($member = null) {
		//if it is already cancelled it can be cancelled again
		if($this->CancelledByID) {
			return true;
		}
		$member = $this->getMemberForCanFunctions($member);
		$extended = $this->extendedCan('canCancel', $member->ID);
		if($extended !== null) {return $extended;}
		if($member->ID) {
			if($member->IsShopAdmin()) {
				return true;
			}
		}
		return $this->MyStep()->CustomerCanCancel;
	}


	/**
	 *
	 *@return Boolean
	 **/
	public function canDelete($member = null) {
		$member = $this->getMemberForCanFunctions($member);
		$extended = $this->extendedCan('canDelete', $member->ID);
		if($extended !== null) {return $extended;}
		return false;
	}




/*******************************************************
   * GET METHODS (e.g. Total, SubTotal, Title, etc...)
*******************************************************/


	/**
	 * see Order::Title()
	 *@return String
	 **/
	function getTitle() {
		return $this->Title();
	}

	/**
	 * A "Title" for the order, which summarises the main details (date, and customer) in a string.
	 *@return String
	 **/
	function Title() {
		if($this->ID) {
			$v = $this->i18n_singular_name(). " #$this->ID - ".$this->dbObject('Created')->Nice();
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
	 * @param string|array $excluded - Class(es) of modifier(s) to ignore in the calculation.
	 * @param Boolean $stopAtExcludedModifier  - when this flag is TRUE, we stop adding the modifiers when we reach an excluded modifier.
	 *
	 * @return Float
	 */
	function ModifiersSubTotal($excluded = null, $stopAtExcludedModifier = false) {
		$total = 0;
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if(!$modifier->IsRemoved()) { //we just double-check this...
					if(is_array($excluded) && in_array($modifier->ClassName, $excluded)) {
						if($stopAtExcludedModifier) {
							break;
						}
						continue;
					}
					elseif($excluded && ($modifier->ClassName == $excluded)) {
						if($stopAtExcludedModifier) {
							break;
						}
						continue;
					}
					$total += $modifier->CalculationTotal();
				}
			}
		}
		return $total;
	}

	/**
	 *
	 * @param string|array $excluded - Class(es) of modifier(s) to ignore in the calculation.
	 * @param Boolean $stopAtExcludedModifier  - when this flag is TRUE, we stop adding the modifiers when we reach an excluded modifier.
	 *
	 *@return Currency (DB Object)
	 **/
	function ModifiersSubTotalAsCurrencyObject($excluded = null, $stopAtExcludedModifier = false) {
		return DBField::create('Currency',$this->ModifiersSubTotal($excluded, $stopAtExcludedModifier));
	}

	/**
	 * Returns the subtotal of the items for this order.
	 *@return float
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

	/**
	 *
	 *@return Currency (DB Object)
	 **/
	function SubTotalAsCurrencyObject() {
		return DBField::create('Currency',$this->SubTotal());
	}

	/**
  	 * Returns the total cost of an order including the additional charges or deductions of its modifiers.
	 *@return float
  	 */
	function Total() {
		return $this->SubTotal() + $this->ModifiersSubTotal();
	}

	/**
	 *
	 *@return Currency (DB Object)
	 **/
	function TotalAsCurrencyObject() {
		return DBField::create('Currency',$this->Total());
	}

	/**
	 * Checks to see if any payments have been made on this order
	 * and if so, subracts the payment amount from the order
	 *
	 *@return float
	 **/
	function TotalOutstanding(){
		$total = $this->Total();
		$paid = $this->TotalPaid();
		$outstanding = $total - $paid;
		if(abs($outstanding) < self::get_maximum_ignorable_sales_payments_difference()) {
			$outstanding = 0;
		}
		return floatval($outstanding);
	}

	/**
	 *
	 *@return Currency (DB Object)
	 **/
	function TotalOutstandingAsCurrencyObject(){
		return DBField::create('Currency',$this->TotalOutstanding());
	}

	/**
	 *
	 *@return Money
	 **/
	function TotalOutstandingAsMoneyObject(){
		$money = DBField::create('Money', array("Amount" => $this->TotalOutstanding(), "Currency" => $this->Currency()));
		return $money;
	}

	/**
	 *@return float
	 */
	function TotalPaid() {
		$paid = 0;
		if($payments = $this->Payments()) {
			foreach($payments as $payment) {
				if($payment->Status == 'Success') {
					$paid += $payment->Amount->getAmount();
				}
			}
		}
		return $paid;
	}

	/**
	 *
	 *@return Currency (DB Object)
	 **/
	function TotalPaidAsCurrencyObject(){
		return DBField::create('Currency',$this->TotalPaid());
	}

	function TotalItems() {
		if(self::$total_items === null) {
			self::$total_items = DB::query("
				SELECT COUNT(\"OrderItem\".\"ID\")
				FROM \"OrderItem\"
					INNER JOIN \"OrderAttribute\" ON \"OrderAttribute\".\"ID\" = \"OrderItem\".\"ID\"
					INNER JOIN \"Order\" ON \"OrderAttribute\".\"OrderID\" = \"Order\".\"ID\"
					INNER JOIN \"OrderStep\" ON \"OrderStep\".\"ID\" = \"Order\".\"StatusID\"
					WHERE
						\"OrderAttribute\".\"OrderID\" = ".$this->ID."
						AND \"OrderItem\".\"Quantity\" > 0
						AND \"OrderStep\".\"CustomerCanEdit\" = 1"
			)->value();
		}
		return self::$total_items;
	}

	function TotalItemsTimesQuantity() {
		$qty = 0;
		if($orderItems = $this->Items()) {
			foreach($orderItems as $item) {
				$qty += $item->Quantity;
			}
		}
		return $qty;
	}

	/**
	 * Returns the country code for the country that applies to the order.
	 * It might be unclear if the Billing or the Shipping Address takes precedent here.
	 *@return String (country code)
	 **/
	public function Country() {
		//Question - what should take precedent: Billing Or Shipping?
		// We have chosen for Billing.
		$countryCodes = array();
		if($this->BillingAddressID) {
			if($billingAddress = DataObject::get_by_id("BillingAddress", $this->BillingAddressID)) {
				$countryCodes[] = $billingAddress->Country;
			}
		}
		if($this->ShippingAddressID) {
			if($shippingAddress = DataObject::get_by_id("ShippingAddress", $this-ShippingAddressID)) {
				$countryCodes[] = $shippingAddress->ShippingCountry;
			}
		}
		if(count($countryCodes)) {
			if(EcommerceCountry::get_use_shipping_address_country_as_default_country()) {
				$countryCodes = array_reverse($countryCodes);
			}
			return array_shift($countryCodes);
		}
	}

	function findShippingCountry($codeOnly = false) {
		user_error("This function has been depreciated", E_USER_WARNING);
	}

	public function FullNameCountry() {
		return EcommerceCountry::find_country_title($this->Country);
	}

	/**
	 * returns the link to view the Order e.g. /account-page/view/12345
	 *@return String(URLSegment)
	 */
	function Link() {
		return AccountPage::get_order_link($this->ID);
	}

	/**
	 * Return a link to the {@link CheckoutPage} instance
	 * that exists in the database.
	 *
	 * @return string
	 */
	function CheckoutLink() {
		return CheckoutPage::find_link();
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




/*******************************************************
   * TEMPLATE RELATED STUFF
*******************************************************/
	// MOVED TO SHOPPING CART



/*******************************************************
   * STANDARD SS METHODS (requireDefaultRecords, onBeforeDelete, etc...)
*******************************************************/



	function populateDefaults() {
		parent::populateDefaults();
		//@Session::start();
		//$this->SessionID = Session_id();
	}

	/**
 	 * Marks if a records has been "init"-ed....
 	 * @var Boolean
 	 **/
	protected $newRecord = true;

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if((isset($this->record['ID']) && $this->record['ID'])) {
			$this->newRecord = false;
		}
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		if($this->newRecord) {
			$this->init();
		}
	}

	/**
	 * delete attributes, statuslogs, and payments
	 */
	function onBeforeDelete(){
		if($attributes = $this->Attributes()){
			foreach($attributes as $attribute){
				$attribute->delete();
				$attribute->destroy();
			}
		}
		if($statuslogs = $this->OrderStatusLogs()){
			foreach($statuslogs as $log){
				$log->delete();
				//$log->destroy();
			}
		}
		if($payments = $this->Payments()){
			foreach($payments as $payment){
				$payment->delete();
				//$payment->destroy();
			}
		}
		if($billingAddress = $this->BillingAddress()) {
			$billingAddress->delete();
			//$billingAddress->destroy();
		}
		if($shippingAddress = $this->ShippingAddress()) {
			$shippingAddress->delete();
			//$shippingAddress->destroy();
		}
		if($emails = $this->Emails()) {
			foreach($emails as $email){
				$email->delete();
				//$email->destroy();
			}
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
		if($this->Items()) {
			$val .= $this->Items()->debug();
		}
		$val .= "<h4>Modifiers</h4>";
		if($this->Modifiers()) {
			$val .= $this->Modifiers()->debug();
		}
		return $val;
	}

}



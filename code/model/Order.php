<?php
/**
 * The order class is a databound object for handling Orders
 * within SilverStripe.
 *
 * @package ecommerce
 */
class Order extends DataObject {

 	/**
 	 */
	public static $db = array(
		'SessionID' => "Varchar(32)", //so that in the future we can link sessions with Orders.... One session can have several orders, but an order can onnly have one session
		'Country' => 'Varchar(3)',
		'UseShippingAddress' => 'Boolean',
		'CustomerOrderNote' => 'Text',
		'ReceiptSent' => 'Boolean',
		'Printed' => 'Boolean'
	);


	public static $has_one = array(
		'Member' => 'Member',
		'ShippingAddress' => 'ShippingAddress',
		'Status' => 'Order_Status'
	);

	public static $has_many = array(
		'Attributes' => 'OrderAttribute',
		'OrderStatusLogs' => 'OrderStatusLog',
		'Payments' => 'Payment',
		'Emails' => 'Order_EmailRecord'
	);

	public static $many_many = array();

	public static $belongs_many_many = array();

	public static $defaults = array();

	public static $indexes = array(
		"SessionID" => true
	);

	public static $default_sort = "\"Created\" DESC";

	public static $casting = array(
		'FullBillingAddress' => 'Text',
		'FullShippingAddress' => 'Text',
		'Total' => 'Currency',
		'SubTotal' => 'Currency',
		'TotalPaid' => 'Currency',
		'Shipping' => 'Currency',
		'TotalOutstanding' => 'Currency',
		'TotalItems' => 'Int',
		'TotalItemsTimesQuantity' => 'Int'
	);

	public static $create_table_options = array(
		'MySQLDatabase' => 'ENGINE=InnoDB'
	);

	public static $singular_name = "Order";

	public static $plural_name = "Orders";


	/**
	 * This is the from address that the receipt
	 * email contains. e.g. "info@shopname.com"
	 *
	 * @var string
	 */
	static function get_receipt_email() {$sc = DataObject::get_one("SiteConfig"); if($sc && $sc->ReceiptEmail) {return $sc->ReceiptEmail;} else {return Email::getAdminEmail();} }

	/**
	 * This is the subject that the receipt
	 * email will contain. e.g. "Joe's Shop Receipt".
	 *
	 * @var string
	 */
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
	public static $table_overview_fields = array(
		'ID' => 'Order No',
		'Created' => 'Created',
		'Member.FirstName' => 'First Name',
		'Member.Surname' => 'Surname',
		'Total' => 'Total',
		'Status' => 'Status'
	);

	public static $summary_fields = array(
		'ID' => 'Order No',
		'Created' => 'Created',
		'Member.Name' => 'First Name',
		'Member.Surname' => 'Surname',
		'Member.Email' => 'Email',
		'Total' => 'Total',
		'TotalOutstanding' => 'Outstanding',
		'Status' => 'Status'
	);

	public static $searchable_fields = array(
		'ID' => array(
			'field' => 'TextField',
			'filter' => 'PartialMatchFilter',
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
		'Member.HomePhone' => array(
			'title' => 'Customer Phone',
			'filter' => 'PartialMatchFilter'
		),
		'Created' => array(
			'field' => 'TextField',
			'filter' => 'OrderFilters_AroundDateFilter',
			'title' => "date"
		),
		'TotalPaid' => array(
			'filter' => 'OrderFilters_MustHaveAtLeastOnePayment',
		),
		'Status.ID' => array(
			'filter' => 'OrderFilters_MultiOptionsetStatusIDFilter',
		)
		/*,
		'To' => array(
			'field' => 'DateField',
			'filter' => 'OrderFilters_EqualOrSmallerDateFilter'
		)
		*/
	);

	protected static $maximum_ignorable_sales_payments_difference = 0.01;
		static function set_maximum_ignorable_sales_payments_difference($v) {self::$maximum_ignorable_sales_payments_difference = $v;}
		static function get_maximum_ignorable_sales_payments_difference() {return self::$maximum_ignorable_sales_payments_difference;}

 	protected static $order_id_start_number = 0;
		static function set_order_id_start_number($v) {self::$order_id_start_number = $v;}
		static function get_order_id_start_number() {return self::$order_id_start_number;}

	public static function get_order_status_options() {
		return DataObject::get("Order_Status");
	}

	function scaffoldSearchFields(){
		$fieldSet = parent::scaffoldSearchFields();
		if($statusOptions = self::get_order_status_options()) {
			$fieldSet->push(new CheckboxSetField("StatusID", "Status", $statusOptions->toDropDownMap()));
		}
		$fieldSet->push(new DropdownField("TotalPaid", "Has Payment", array(1 => "yes", 0 => "no")));
		return $fieldSet;
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->insertBefore(new LiteralField('Title',"<h2>Order #$this->ID - ".$this->dbObject('Created')->Nice()." - ".$this->Member()->getName()."</h2>"),'Root');
		$fieldsAndTabsToBeRemoved[] = array('Printed', 'MemberID', 'Attributes', 'SessionID');
		foreach($fieldsAndTabsToBeRemoved as $field) {
			$fields->removeByName($field);
		}

		$htmlSummary = $this->renderWith("Order");

		$printlabel = (!$this->Printed) ? "Print Invoice" : "Print Invoice Again"; //TODO: i18n
		$fields->addFieldsToTab('Root.Main', array(
			new LiteralField("PrintInvoice",'<p class="print"><a href="OrderReport_Popup/index/'.$this->ID.'?print=1" onclick="javascript: window.open(this.href, \'print_order\', \'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50\'); return false;">'.$printlabel.'</a></p>')
		));

		$fields->addFieldToTab('Root.Main', new LiteralField('MainDetails', $htmlSummary));

		//TODO: re-introduce this when order status logs have some meaningful purpose
		//$fields->removeByName('OrderStatusLogs');

		$orderItemsTable = new TableListField(
			"OrderItems", //$name
			"OrderItem", //$sourceClass =
			OrderItem::$summary_fields, //$fieldList =
			"\"OrderID\" = ".$this->ID, //$sourceFilter =
			"\"Created\" ASC", //$sourceSort =
			null //$sourceJoin =
		);
		if($this->MyStatus()->CanEdit()) {
			$orderItemsTable->setPermissions(array("view", "edit", "delete"));
		}
		else {
			$orderItemsTable->setPermissions(array("view"));
		}
		$orderItemsTable->setPageSize(100);
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
		if($this->MyStatus()->CanEdit()) {
			$modifierTable->setPermissions(array("view", "edit", "delete"));
		}
		else {
			$modifierTable->setPermissions(array("view"));
		}
		$modifierTable->setPageSize(100);
		$fields->addFieldToTab('Root.Extras',$modifierTable);
		if($m = $this->Member()) {
			$lastv = new TextField("MemberLastLogin","Last login",$m->dbObject('LastVisited')->Nice());
			$fields->addFieldToTab('Root.Customer',$lastv->performReadonlyTransformation());
			//TODO: this should be scaffolded instead, or come from something like $member->getCMSFields();
			$fields->addFieldToTab('Root.Customer',new LiteralField("MemberSummary", $m->renderWith("Order_Member")));
		}
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}

	/**
	 * Set the fields to be used for {@link ComplexTableField}
	 * tables for Order instances, such as for reports. This
	 * sets the {@link Order::$table_overview_fields} variable.
	 *
	 * @param array $fields An array of fields to show
	 */
	public static function set_table_overview_fields($fields) {
		self::$table_overview_fields = $fields;
	}


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
					if($modifier instanceof OrderModifier && eval("return $className::show_form();") && $form = eval("return $className::get_form(\$controller);")) array_push($forms, $form);
				}
			}
		}

		return count($forms) > 0 ? new DataObjectSet($forms) : null;
	}

	/**
	 * Transitions the order from being in the Cart to being in an unpaid post-cart state.
	 *
	 * @return Order The current order
	 */
	function save() {
		if($newStatus = DataObject::get_one("Order_Status", "\"CanEdit\" = 0")) {
			$this->StatusID = $newStatus->ID;
		}
		else {
			user_error("There is no order_status with CanEdit = 0. This is needed to be able to save an order", E_USER_WARNING);
		}
		//re-write all attributes and modifiers to make sure they are up-to-date before they can't be changed again
		if($this->Attributes()->exists()){
			foreach($this->Attributes() as $attribute){
				$attribute->write();
			}
		}
		$this->extend('onSave'); //allow decorators to do stuff when order is saved.
		$this->write();
	}

	// Items Management

	/**
	 * Returns the items of the order, if it hasn't been saved yet
	 * it returns the items from session, if it has, it returns them
	 * from the DB entry.
	 */
	function Items($filter = "") {
 		if($this->ID){
 			return $this->itemsFromDatabase($filter);
 		}
 		elseif($items = ShoppingCart::get_items()){
 			return $this->createItems($items);
 		}
	}

	/**
	 * Return all the {@link OrderItem} instances that are
	 * available as records in the database.
	 *
	 * @return DataObjectSet
	 */
	protected function itemsFromDatabase($filter = null) {
		$extrafilter = ($filter) ? " AND $filter" : "";
		$dbitems =  DataObject::get("OrderItem", "\"OrderID\" = '$this->ID' $extrafilter");
		return $dbitems;
	}

	/**
	 * Return a DataObjectSet of {@link OrderItem} objects.
	 *
	 * If the write parameter is set to true, then each of
	 * the item objects in the array are linked to this
	 * order, then written to the database.
	 *
	 * @param array $items An array of {@link OrderItem} objects
	 * @param boolean $write Flag if set to true, will write the items to the DB
	 * @return DataObjectSet
	 */
	protected function createItems(array $items, $write = false) {
		if($write) {
			foreach($items as $item) {
				$item->OrderID = $this->ID;
				$item->write();
			}
		}
		return $write ? $this->itemsFromDatabase() : new DataObjectSet($items);
	}


	/**
	 * Initialise all the {@link OrderModifier} objects
	 * by evaluating init_for_order() on each of them.
	 */
	function initModifiers() {
		//check if order has modifiers already
		//check /re-add all non-removable ones
		$createdmodifiers = $this->Modifiers();
		if(self::$modifiers && is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $className) {
				if(class_exists($className) && (!$createdmodifiers || !$createdmodifiers->find('ClassName',$className))) {
					$modifier = new $className();
					if($modifier instanceof OrderModifier) eval("$className::init_for_order(\$className);");
				}
			}
		}
	}


	/**
	 * Returns the modifiers of the order, if it hasn't been saved yet
	 * it returns the modifiers from session, if it has, it returns them
	 * from the DB entry.
	 */
 	function Modifiers() {
 		$mods = false;
 		if($this->ID) {
 			$mods = $this->modifiersFromDatabase();
 		}
		elseif($modifiers = ShoppingCart::get_modifiers()) {
 			$mods = $this->createModifiers($modifiers);
 		}
 		return $mods;
	}

	/**
	 * Get all {@link OrderModifier} instances that are
	 * available as records in the database.
	 *
	 * @return DataObjectSet
	 */
	protected function modifiersFromDatabase() {
		//TO DO: why do we need to have the sort stuff here???
		return DataObject::get('OrderModifier', $where = "\"OrderID\" = '$this->ID'", $sort = "\"ID\" ASC");
	}

	/**
	 * Return a DataObjectSet of {@link OrderModifier} objects.
	 *
	 * {@link Order->Modifiers()} makes use of this method.
	 *
	 * If the write parameter is set to true, then each of
	 * the modifier objects in the array are linked to this
	 * order, then written to the database.
	 *
	 * @param array $modifiers An array of {@link OrderModifier} objects
	 * @param boolean $write Flag if set to true, will write the modifiers to the DB
	 * @return DataObjectSet
	 */
	protected function createModifiers(array $modifiers, $write = false) {
		if($write) {
			foreach($modifiers as $modifier) {
				$modifier->OrderID = $this->ID;
				$modifier->write();
			}
		}
		return $write ? $this->modifiersFromDatabase() : new DataObjectSet($modifiers);
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
					if($onlyprevious)
						break;
					continue;
				} elseif($excluded && ($modifier->class == $excluded)) {
					if($onlyprevious)
						break;
					continue;
				}

				$total += $modifier->Total();
			}
		}

		return $total;
	}

	// Order Management

	/**
	 * Returns the subtotal of the items for this order.
	 */
	function SubTotal() {
		$result = 0;
		if($items = $this->Items()) {
			foreach($items as $item) $result += $item->Total();
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
					$paid += $payment->Amount->getAmount();
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

	/**
	 * @TODO Why do we need to get this from the AccountPage class?
	 */
	function Link() {
		return AccountPage::get_order_link($this->ID);
	}

	/**
	 * Returns TRUE if the order can be cancelled
	 * PRECONDITION: Order is in the DB.
	 *
	 * @return boolean
	 */
	function canCancel() {
		return $this->MyStatus()->CanCancel;
	}

	public function canDelete($member = null) {
		return false;
	}

	public function canEdit($member = null) {
		return true;
	}

	public function canCreate($member = null) {
		//TO DO: setup a special group of shop admins (probably can copy some code from Blog)
		if($member->IsAdmin()) {
			return true;
		}
		return false;
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

	function getFullBillingAddress($separator = "",$insertnewlines = true){
		//TODO: move this somewhere it can be customised
		$toUse = array(
			'Name',
			'Company',
			'Address',
			'AddressLine2',
			'City',
			'Country',
			'Email',
			'Phone',
			'HomePhone',
			'MobilePhone'
		);

		$fields = array();
		$member = $this->Member();
		foreach($toUse as $field){
			if($member && $member->$field)
				$fields[] = $member->$field;
		}
		$separator = ($insertnewlines) ? $separator."\n" : $separator;
		return implode($separator,$fields);
	}

	function getFullShippingAddress($separator = "",$insertnewlines = true){
		if(!$this->UseShippingAddress) {
			return $this->getFullBillingAddress($separator,$insertnewlines);
		}
		$toUse = ShippingAddress::get_shipping_fields();
		$object = $this->ShippingAddress();
		$fields = array();
		foreach($toUse as $field){
			if($object->$field) {
				$fields[] = $object->$field;
			}
		}
		$separator = ($insertnewlines) ? $separator."\n" : $separator;
		return implode($separator,$fields);
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
		$total = $this->TotalAsCurrencyObject()->Nice() . ' ' . $this->Currency();
		$js[] = array('id' => $this->TableSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->OrderForm_OrderForm_AmountID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
	}

	/**
	 * Will update payment status to "Paid if there is no outstanding amount".
	 */
	function updatePaymentStatus(){
		if($this->IsPaid() && $this->MyStatus()->CanPay){
			$newStatus = DataObject::get_one("Order_Status", "\"CanPay\" = 0");
			//TODO: only run this if it is setting to Paid, and not cancelled or similar
			$this->StatusID = $newStatus->ID;
			$this->write();
		}
	}

	function MyStatus() {
		$obj = null;
		if($this->StatusID) {
			$obj = DataObject::get_by_id("Order_Status", $this->StatusID);
		}
		if(!$obj) {
			$obj = DataObject::get_one("Order_Status");
		}
		$this->StatusID = $obj->ID;
		return $obj;
	}

	/**
	 * @return boolean
	 */
	function IsSent() {
		return !$this->MyStatus()->Unsent;
	}

	/**
	 * @return boolean
	 */
	function IsProcessing() {
		return $this->MyStatus()->Uncompleted && !$this->MyStatus()->CanEdit;
	}

	/**
	 * @return boolean
	 */
	function IsPaid() {
		return $this->Total() > 0 && $this->TotalOutstanding() <= 0;
	}

	/**
	 * @return boolean
	 */
	function IsCart(){
		return $this->MyStatus()->CanEdit;
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
	 * Send the receipt of the order by mail.
	 * Precondition: The order payment has been successful
	 */
	function sendReceipt() {
		$this->sendEmail('Order_ReceiptEmail');
		$this->ReceiptSent = true;
		$this->write();
	}

	/**
	 * Send a mail of the order to the client (and another to the admin).
	 *
	 * @param $emailClass - the class name of the email you wish to send
	 * @param $copyToAdmin - true by default, whether it should send a copy to the admin
	 */
	protected function sendEmail($emailClass, $copyToAdmin = true) {
 		$from = self::get_receipt_email();
 		$to = $this->Member()->Email;
		$subject = self::get_receipt_subject();
		$subject = str_replace("{OrderNumber}", $this->ID, $subject);
		//TO DO: should be a payment specific message as well???
		$purchaseCompleteMessage = DataObject::get_one('CheckoutPage')->PurchaseComplete;
 		$email = new $emailClass();
 		$email->setFrom($from);
 		$email->setTo($to);
 		$email->setSubject($subject);
		if($copyToAdmin) {
			$email->setBcc(Email::getAdminEmail());
		}
		$email->populateTemplate(
			array(
				'PurchaseCompleteMessage' => $purchaseCompleteMessage,
				'Order' => $this
			)
		);
		$email->send();
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

	/**
	 * Returns a TaxModifier object that provides
	 * information about tax on this order.
	 *
	 * @return TaxModifier
	 */
	function TaxInfo() {
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if($modifier instanceof TaxModifier) return $modifier;
			}
		}
	}

	/**
	 * Send a message to the client containing the latest
	 * note of {@link OrderStatusLog} and the current status.
	 *
	 * Used in {@link OrderReport}.
	 *
	 * @param string $note Optional note-content (instead of using the OrderStatusLog)
	 */
	function sendStatusChange($title, $note = null) {
		if(!$note) {
			$logs = DataObject::get('OrderStatusLog', "\"OrderID\" = {$this->ID} AND \"EmailCustomer\" = 1 AND \"EmailSent\" = 0 ", "\"Created\" DESC", null, 1);
			if($logs) {
				$latestLog = $logs->First();
				$note = $latestLog->Note;
				$title = $latestLog->Title;
			}
		}
		$member = $this->Member();
 		if(self::$receipt_email) {
 			$adminEmail = self::$receipt_email;
 		}
		else {
 			$adminEmail = Email::getAdminEmail();
 		}
		$e = new Order_statusEmail();
		$e->populateTemplate($this);
		$e->populateTemplate(
			array(
				"Order" => $this,
				"Member" => $member,
				"Note" => $note
			)
		);
		$e->setFrom($adminEmail);
		$e->setSubject($title);
		$e->setTo($member->Email);
		$e->send();
	}

	function updatePrinted($printed){
		$this->__set("Printed", $printed);
		$this->write();
	}

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
 			if($orders = DataObject::get('Order', "\"UseShippingAddress\" = 1  OR (\"ShippingName\" IS NOT NULL AND \"ShippingName\" <> ''")) {
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
			$adminCancelledObject = DataObject::get_one("Order_Status", "\"AdminCancelled\" = 1");
			if($orders && $adminCancelledObject) {
				foreach($orders as $order) {
					if(!$order->StatusID && $adminCancelledObject) {
						$order->StatusID = $adminCancelledObject->ID;
					}
					$order->Status = 'AdminCancelled';
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
								if(!($CartObject = DataObject::get_one("Order_Status", "\"CanEdit\" = 1"))) {
									$CartObject = new Order_Status();
									$CartObject->Name = "Cart";
									$CartObject->CanEdit = $CartObject->CanCancel = $CartObject->CanPay = $CartObject->Uncollated = $CartObject->Unsent = 1;
									$CartObject->ShowAsUncompletedOrder = 1;
									$CartObject->write();
									DB::alteration_message("Created CART Order Status", "created");
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$CartObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							break;
						case "Query":
						case "Unpaid":
							if(!$UnpaidObject) {
								if(!($UnpaidObject = DataObject::get_one("Order_Status", "\"CanEdit\" = 0 AND \"CanPay\" = 1"))) {
									$UnpaidObject = new Order_Status();
									$UnpaidObject->Name = "Unpaid";
									$UnpaidObject->CanCancel = $UnpaidObject->CanPay = $UnpaidObject->Uncollated = $UnpaidObject->Unsent = 1;
									$UnpaidObject->ShowAsUncompletedOrder = 1;
									$UnpaidObject->write();
									DB::alteration_message("Created Unpaid Order Status", "created");
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$UnpaidObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							break;
						case "Processing":
						case "Paid":
							if(!$PaidObject) {
								if(!($PaidObject = DataObject::get_one("Order_Status", "\"CanEdit\" = 0 AND \"CanPay\" = 0"))) {
									$PaidObject = new Order_Status();
									$PaidObject->Name = "Paid";
									$PaidObject->Uncollated = $PaidObject->Unsent = 1;
									$PaidObject->ShowAsInProcessOrder = 1;
									$PaidObject->write();
									DB::alteration_message("Created Paid Order Status", "created");
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$PaidObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							break;
						case "Sent":
						case "Complete":
							if(!$PaidObject) {
								if(!($SentObject = DataObject::get_one("Order_Status", "\"Unsent\" = 0"))) {
									$SentObject = new Order_Status();
									$SentObject->Name = "Sent";
									$SentObject->ShowAsCompletedOrder = 1;
									$SentObject->write();
									DB::alteration_message("Created Sent Order Status", "created");
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$SentObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							break;
						case "AdminCancelled":
							if(!$AdminCancelledObject) {
								if(!($AdminCancelledObject = DataObject::get_one("Order_Status", "\"AdminCancelled\" = 1"))) {
									$AdminCancelledObject = new Order_Status();
									$AdminCancelledObject->Name = "Admin Cancelled";
									$AdminCancelledObject->AdminCancelled = 1;
									$AdminCancelledObject->write();
									DB::alteration_message("Created Admin Cancelled Order Status", "created");
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$AdminCancelledObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							break;
						case "MemberCancelled":
							if(!$MemberCancelledObject) {
								if(!($MemberCancelledObject = DataObject::get_one("Order_Status", "\"CustomerCancelled\" = 1"))) {
									$MemberCancelledObject = new Order_Status();
									$MemberCancelledObject->Name = "Customer Cancelled";
									$MemberCancelledObject->CustomerCancelled = 1;
									$MemberCancelledObject->write();
									DB::alteration_message("Created Customeer Cancelled Order Status", "created");
								}
							}
							DB::query("UPDATE \"Order\" SET StatusID = ".$MemberCancelledObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
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

class Order_EmailRecord extends DataObject {

	public static $db = array(
		"From" => "Varchar(255)",
		"To" => "Varchar(255)",
		"Subject" => "Varchar(255)",
		"Content" => "HTMLText",
		"Result" => "Boolean"
	);
	public static $has_one = array(
		"Order" => "Order",
		"Member" => "Member"
	);
	public static $summary_fields = array(
		"Created" => "Send",
		"From" => "From",
		"To" => "To",
		"Subject" => "Subject",
		"Result" => "Sent Succesfully"
	);
	public static $singular_name = "Customer Email";
	public static $plural_name = "Customer Emails";
	//CRUD settings
	public function canCreate($member = null) {return false;}
	public function canEdit($member = null) {return false;}
	public function canDelete($member = null) {return false;}
	//defaults
	public static $default_sort = "\"Created\" DESC";

}

class Order_Email extends Email {

	public function send($messageID = null) {
		$result = parent::send($messageID);
		$this->CreateRecord($result);
		return $result;
	}

	public function sendPlain($messageID = null) {
		$result = parent::sendPlain($messageID);
		$this->CreateRecord($result);
		return $result;

	}

	protected function CreateRecord($result) {
		$obj = new Order_EmailRecord();
		$obj->From = $this->from;
		$obj->To = $this->to;
		$obj->Subject = $this->subject;
		$obj->Content = $this->body;
		$obj->Result = $result;
		if(Email::$send_all_emails_to) {
			$obj->To = Email::$send_all_emails_to;
		}
		$obj->write();
	}

}

/**
 * This class handles the receipt email which gets sent once an order is made.
 * You can call it by issuing sendReceipt() in the Order class.
 */
class Order_ReceiptEmail extends Order_Email {

	protected $ss_template = 'Order_ReceiptEmail';

}

/**
 * This class handles the status email which is sent after changing the attributes
 * in the report (eg. status changed to 'Shipped').
 */
class Order_StatusEmail extends Order_Email {

	protected $ss_template = 'Order_StatusEmail';

}

class Order_CancelForm extends Form {

	function __construct($controller, $name, $orderID) {
		$fields = new FieldSet(
			new HiddenField('OrderID', '', $orderID)
		);
		$actions = new FieldSet(
			new FormAction('doCancel', _t('Order.CANCELORDER','Cancel this order'))
		);
		parent::__construct($controller, $name, $fields, $actions);
	}

	/**
	 * Form action handler for Order_CancelForm.
	 *
	 * Take the order that this was to be change on,
	 * and set the status that was requested from
	 * the form request data.
	 *
	 * @param array $data The form request data submitted
	 * @param Form $form The {@link Form} this was submitted on
	 */
	function doCancel($data, $form) {
		$SQLData = Convert::raw2sql($data);
		$member = $this->CurrentMember();
		if(isset($SQLData['OrderID']) && $order = DataObject::get_one('Order', "\"ID\" = ".$SQLData['OrderID']." AND \"MemberID\" = $member->ID")){
			if($order->canCancel()) {
				if($newStatus = DataObject::get_one("Order_Status", "\"CustomerCancelled\" = 1")) {
					$order->StatusID = $newStatus->ID;
					$order->write();
				}
				else {
					user_error("There is no CustomerCancelled Order_Status DataObject", "E_USER_NOTICE");
				}
			}
			else {
				user_error("Tried to cancel an order that can not be cancelled with Order ID: ".$order->ID, "E_USER_NOTICE");
			}
		}
		//TODO: notify people via email??
		if($link = AccountPage::find_link()){
			//TODO: set session message "order successfully cancelled".
			Director::redirect($link);
		}
		else{
			Director::redirectBack();
		}
		return;
	}

}



class Order_Status extends DataObject {
	//database
	public static $db = array(
		"Name" => "Varchar(50)",
		"Description" => "Text",
		//customer can still edit....
		"CanEdit" => "Boolean",
		"CanCancel" => "Boolean",
		"CanPay" => "Boolean",
		//order gets processed
		"Uncollated" => "Boolean", //putting order together
		"Unsent" => "Boolean", // once sent, it is regards as completed
		//special cases:
		"OnHold" => "Boolean",
		"AdminCancelled" => "Boolean",
		"CustomerCancelled" => "Boolean",
		//What to show the customer...
		"ShowAsUncompletedOrder" => "Boolean",
		"ShowAsInProcessOrder" => "Boolean",
		"ShowAsCompletedOrder" => "Boolean",
		"Sort" => "Int"
	);
	public static $indexes = array(
		"Name" => true
	);
	public static $has_many = array(
		"Order" => "Order"
	);
	public static $field_labels = array(
	);
	public static $summary_fields = array(
		"Name" => "Name",
		"CanEdit" => "CanEdit",
		"CanCancel" => "CanCancel",
		"CanPay" => "CanPay",
		"Uncollated" => "Uncollated",
		"Unsent" => "Unsent",
		"OnHold" => "OnHold",
		"AdminCancelled" => "AdminCancelled",
		"CustomerCancelled" => "CustomerCancelled",
		"ShowAsUncompletedOrder" => "ShowAsUncompletedOrder",
		"ShowAsInProcessOrder" => "ShowAsInProcessOrder",
		"ShowAsCompletedOrder" => "ShowAsCompletedOrder"
	);
	public static $singular_name = "Order Status Option";
		static function get_singular_name() {return self::$singular_name;}
		static function set_singular_name($v) {self::$singular_name = $v;}

	public static $plural_name = "Order Status Options";

	public static $default_sort = "\"CustomerCancelled\" ASC, \"AdminCancelled\" ASC, \"OnHold\" ASC, \"Unsent\" DESC, \"Uncollated\" DESC, \"CanPay\" DESC, \"CanCancel\" DESC, \"CanEdit\" DESC, \"Sort\" ASC";

	public static $defaults = array();//use fieldName => Default Value

	//Unpaid,Query,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled,Cart
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!DataObject::get_one("Order_Status", "\"CanEdit\" = 1")) {
			$obj = new Order_Status();
			$obj->Name = "Uncompleted";
			$obj->CanEdit = $obj->CanCancel = $obj->CanPay = $obj->Uncollated = $obj->Unsent = 1;
			$obj->ShowAsUncompletedOrder = 1;
			$obj->write();
			DB::alteration_message("Created Editable (Uncompleted) Order Status", "created");
		}
		if(!DataObject::get_one("Order_Status", "\"CanEdit\" = 0 AND \"Unsent\" = 0")) {
			$obj = new Order_Status();
			$obj->Name = "Processing";
			$obj->Uncollated = $obj->Unsent = 1;
			$obj->ShowAsInProcessOrder = 1;
			$obj->write();
			DB::alteration_message("Created Non-Editable, Unpaid Order Status", "created");
		}
		if(!DataObject::get_one("Order_Status", "\"Unsent\" = 0")) {
			$obj = new Order_Status();
			$obj->Name = "Completed";
			$obj->ShowAsCompletedOrder = 1;
			$obj->write();
			DB::alteration_message("Created Completed Cancelled Order Status", "created");
		}
		if(!DataObject::get_one("Order_Status", "\"AdminCancelled\" = 1")) {
			$obj = new Order_Status();
			$obj->Name = "Admin Cancelled";
			$obj->AdminCancelled = 1;
			$obj->write();
			DB::alteration_message("Created Admin Cancelled Order Status", "created");
		}
		if(!DataObject::get_one("Order_Status", "\"CustomerCancelled\" = 1")) {
			$obj = new Order_Status();
			$obj->Name = "Customer Cancelled";
			$obj->CustomerCancelled = 1;
			$obj->write();
			DB::alteration_message("Created Customer Cancelled Order Status", "created");
		}
	}

	function getCMSFields() {
		//TO DO: add warning messages and break up fields
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING1", _t("Order.CAREFUL", "CAREFUL! please edit with care"), 1), "Name");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING2", _t("Order.CUSTOMERCANCHANGE", "Customer can still make changes..."), 3), "CanEdit");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING3", _t("Order.ORDERPROCESSED", "Order is being processed..."), 3), "Uncollated");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING4", _t("Order.SPECIALCASES", "Special cases..."), 3), "OnHold");
		$fields->addFieldToTab("Root.Main", new HeaderField("WARNING5", _t("Order.ORDERGROUPS", "Order groups for customer?"), 3), "ShowAsUncompletedOrder");
		return $fields;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		$i = 0;
		while(!$this->Name || DataObject::get_one($this->ClassName, "\"Name\" = '".$this->Name."' AND \"".$this->ClassName."\".\"ID\" <> ".intval($this->ID))) {
			$this->Name = self::get_singular_name();
			if($i) {
				$this->Name .= "_".$i;
			}
			$i++;
		}
		//enforce logical order...
		if($this->CanEdit) {
			$this->CanPay = $this->Uncollated = $this->Unsent = 1;
		}
		if(!$this->CanEdit && $this->CanPay) {
			$this->Uncollated = $this->Unsent = 1;
		}
		if(!$this->CanEdit && !$this->CanPay && $this->Uncollated) {
			$this->Unsent = 1;
		}
	}
	public function canDelete() {
		if($order = DataObject::get_one("Order", "StatusID = ".$this->ID)) {
			return false;
		}
		return true;
	}

}


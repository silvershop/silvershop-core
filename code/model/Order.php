<?php
/**
 * The order class is a databound object for handling Orders
 * within SilverStripe.
 *
 * @package shop
 */
class Order extends DataObject {

 	/**
 	 * Status codes and what they mean:
 	 *
 	 * Unpaid (default): Order created but no successful payment by customer yet
 	 * Query: Order not being processed yet (customer has a query, or could be out of stock)
 	 * Paid: Order successfully paid for by customer
 	 * Processing: Order paid for, package is currently being processed before shipping to customer
 	 * Sent: Order paid for, processed for shipping, and now sent to the customer
 	 * Complete: Order completed (paid and shipped). Customer assumed to have received their goods
 	 * AdminCancelled: Order cancelled by the administrator
 	 * MemberCancelled: Order cancelled by the customer (Member)
 	 */
	public static $db = array(
		'Total' => 'Currency',
		'Reference' => 'Varchar', //allow for customised order numbering schemes
		//status
		'Placed' => "SS_Datetime", //date the order was placed (went from Cart to Order)
		'Paid' => 'SS_Datetime', //no outstanding payment left
		'ReceiptSent' => 'SS_Datetime', //receipt emailed to customer
		'Printed' => 'SS_Datetime',
		'Dispatched' => 'SS_Datetime', //products have been sent to customer
		'Status' => "Enum('Unpaid,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled,Cart','Cart')",
		//customer (for guest orders)
		'FirstName' => 'Varchar',
		'Surname' => 'Varchar',
		'Email' => 'Varchar',
		'Notes' => 'Text',
		'IPAddress' => 'Varchar(15)',
		//separate shipping
		'SeparateBillingAddress' => 'Boolean'
	);

	public static $has_one = array(
		'Member' => 'Member',
		'ShippingAddress' => 'Address',
		'BillingAddress' => 'Address'
	);

	public static $has_many = array(
		'Items' => 'OrderItem',
		'Modifiers' => 'OrderModifier',
		'OrderStatusLogs' => 'OrderStatusLog',
		'Payments' => 'Payment'
	);
	
	public static $default_sort = "\"Placed\" DESC, \"Created\" DESC";
	
	public static $defaults = array(
		'Status' => 'Cart'
	);
	
	public static $casting = array(
		'FullBillingAddress' => 'Text',
		'FullShippingAddress' => 'Text',
		'Total' => 'Currency',
		'SubTotal' => 'Currency',
		'TotalPaid' => 'Currency',
		'Shipping' => 'Currency',
		'TotalOutstanding' => 'Currency'
	);

	public static $singular_name = "Order";
	public static $plural_name = "Orders";
	static $admin_template = "Order";

	/**
	 * Statuses for orders that have been placed.
	 */
	static $placed_status = array('Paid','Unpaid', 'Processing', 'Sent', 'Complete', 'MemberCancelled', 'AdminCancelled');

	/**
	 * Statuses that shouldn't show in user account.
	 */
	static $hidden_status = array('Cart','Query');

	/**
	 * Flag to determine whether the user can cancel
	 * this order before payment is received.
	 *
	 * @var boolean
	 */
	protected static $can_cancel_before_payment = true;

	/**
	 * Flag to determine whether the user can cancel
	 * this order before processing has begun.
	 *
	 * @var boolean
	 */
	protected static $can_cancel_before_processing = false;

	/**
	 * Flag to determine whether the user can cancel
	 * this order before the goods are sent.
	 *
	 * @var boolean
	 */
	protected static $can_cancel_before_sending = false;

	/**
	 * Flag to determine whether the user can cancel
	 * this order after the goods are sent.
	 *
	 * @var unknown_type
	 */
	protected static $can_cancel_after_sending = false;

	/**
	 * Modifiers represent the additional charges or
	 * deductions associated to an order, such as
	 * shipping, taxes, vouchers etc.
	 *
	 * @var array
	 */
	protected static $modifiers = array();
	
	/**
	 * Store total after calculation
	 * @var unknown_type
	 */
	protected $total = 0;
	
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
		'Reference' => 'Order No',
		'Placed' => 'Date',
		'FirstName' => 'First Name',
		'Surname' => 'Surname',
		'Total' => 'Total',
		'Status' => 'Status'
	);

	public static $summary_fields = array(
		'Reference' => 'Order No',
		'Placed' => 'Date',
		'Name' => 'Customer',
		'LatestEmail' => 'Email',
		'Total' => 'Total',
		'Status' => 'Status'
	);

	public static $searchable_fields = array(
		'Reference' => array(
			'field' => 'TextField',
			'filter' => 'PartialMatchFilter',
			'title' => 'Reference'
		),
		'FirstName' => array(
			'title' => 'Customer Name',
			'filter' => 'PartialMatchFilter'
		),
		'Email' => array(
			'title' => 'Customer Email',
			'filter' => 'PartialMatchFilter'
		),
		'Placed' => array(
			'field' => 'TextField',
			'filter' => 'OrderFilters_AroundDateFilter',
			'title' => "Date"
		),
		'Status' => array(
			'filter' => 'OrderFilters_MultiOptionsetFilter',
		)
	);

	public static $rounding_precision = 2;
	public static $reference_id_padding = 5;
	
	protected static $maximum_ignorable_sales_payments_difference = 0.01;
	public static function set_maximum_ignorable_sales_payments_difference($difference){
		self::$maximum_ignorable_sales_payments_difference = $difference;
	}

	public static function get_order_status_options() {
		return singleton('Order')->dbObject('Status')->enumValues(false);
	}

	function scaffoldSearchFields(){
		$fieldSet = parent::scaffoldSearchFields();
		$values = self::$placed_status;
		$fields = array_combine(self::$placed_status,self::$placed_status);
		$fieldSet->push(new CheckboxSetField("Status", "Status",$fields,$values));
		return $fieldSet;
	}
	
	/**
	 * Create CMS fields for cms viewing and editing orders
	 * Also note that some fields are introduced in OrdersAdmin_RecordController 
	 */
	function getCMSFields(){
		$fields = new FieldSet(new TabSet('Root',new Tab('Main')));
		$fields->insertBefore(new HeaderField('Title',"Order #".$this->getReference()),'Root');
		$fields->insertBefore(new LiteralField('SubTitle',
			"<h4 class=\"subtitle\">".$this->dbObject('Placed')->Nice()." - <a href=\"mailto:".$this->getLatestEmail()."\">".$this->getName()."</a></h4>"
		),"Root");
		
		$fields->addFieldsToTab('Root.Main', array(
			new DropdownField("Status","Status", self::get_order_status_options()),
			new LiteralField('MainDetails', $this->renderWith(self::$admin_template))
		));
		$payments = new TableListField(
			"Payments", //$name
			"Payment", //$sourceClass =
			Payment::$summary_fields, //$fieldList =
			"\"OrderID\" = ".$this->ID, //$sourceFilter =
			"\"Created\" ASC", //$sourceSort =
			null //$sourceJoin =
		);
		$payments->setPermissions(array("view"));
		$payments->setPageSize(20);
		$payments->addSummary("Total",array("Total" => array("sum","Currency->Nice")));
		$fields->addFieldToTab('Root.Payments',$payments);
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}
	
	/**
	 * Get fields for order form
	 * 
	 * @return FieldSet
	 */
	function getFormFields() {
		$fields = new FieldSet(
			new TextField('FirstName', _t('Order.FIRSTNAME','First Name')),
			new TextField('Surname', _t('Order.SURNAME','Surname')),
			new EmailField('Email', _t('Order.EMAIL','Email')),
			new HeaderField("ShippingHeading",_t('OrderForm.ShippingAndBillingAddress','Shipping and Billing Address'), 3)
		);
		$fields->merge(singleton('Address')->getFormFields("Shipping"));
		$this->owner->extend('updateFormFields', $fields);
		$this->owner->extend('augmentEcommerceFields', $fields); //deprecated
		return $fields;
	}
	
	/**
	 * Return which fields should be required on {@link OrderForm}
	 * and {@link ShopAccountForm}.
	 *
	 * @return array
	 */
	function getRequiredFields() {
		$fields = array(
			'FirstName',
			'Email'
		);
		$fields = array_merge($fields,singleton('Address')->getRequiredFields("Shipping"));
		$this->owner->extend('updateRequiredFields', $fields);
		$this->owner->extend('augmentEcommerceRequiredFields', $fields); //deprecated
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
	 * Set the flag to determine whether a user can
	 * cancel their order before payment.
	 *
	 * @param boolean $value
	 */
	public static function set_cancel_before_payment($value) {
		self::$can_cancel_before_payment = $value;
	}

	/**
	 * Set the flag to determine whether a user can
	 * cancel their order before processing begins.
	 *
	 * @param unknown_type $value
	 */
	public static function set_cancel_before_processing($value) {
		self::$can_cancel_before_processing = $value;
	}

	/**
	 * Set the flag to determine whether a user can
	 * cancel their order before it is sent.
	 *
	 * @param boolean $value
	 */
	public static function set_cancel_before_sending($value) {
		self::$can_cancel_before_sending = $value;
	}

	/**
	 * Set the flag to determine whether a user can
	 * cancel their order after it has been sent.
	 *
	 * @param boolean $value
	 */
	public static function set_cancel_after_sending($value) {
		self::$can_cancel_after_sending = $value;
	}

	protected static $set_can_cancel_on_status = array();

	static function set_can_cancel_on_status($array) {
		//TODO: check that the stati provided in array actually exist
		self::$set_can_cancel_on_status = $array;
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

	// Items Management

	/**
	 * Returns the subtotal of the items for this order.
	 */
	function SubTotal() {
		$result = 0;
		if($items = $this->Items()) {
			foreach($items as $item){
				$result += $item->Total();
			}
		}
		return $result;
	}
	
	/**
	 * Creates (if necessary) and calculates values for each modifier,
	 * and subsequently the total of the order.
	 * Caches to prevent recalculation, unless dirty.
	 * 
	 * @return the final total
	 * @todo remove empty modifiers? ...perhaps create some kind of 'cleanup' function?
	 * @todo prevent this function from being run too many times
	 */
	function calculate(){
		$runningtotal = $this->SubTotal();
		$modifiertotal = 0;
		$sort = 1;
		$existingmodifiers = $this->Modifiers();
		
		if($this->IsCart()){
			//check if modifiers are even in use
			if(!self::$modifiers || !is_array(self::$modifiers) || count(self::$modifiers) <= 0){
				return $this->Total = $runningtotal;
			}
			foreach(self::$modifiers as $ClassName){
				if($modifier = $this->getModifier($ClassName)){
					$modifier->Sort = $sort;
					$runningtotal = $modifier->modify($runningtotal);
					if($modifier->isChanged()){
						$modifier->write();
					}
				}
				$sort++;
			}
			//clear out modifiers that shouldn't be there, according to defined modifiers list
				//TODO: it may be better to store/run this as a build task - remove all invalid modifiers from carts
			if($existingmodifiers){
				foreach($existingmodifiers as $modifier){
					if(!in_array($modifier->ClassName,self::$modifiers)){
						$modifier->delete();
						$modifier->destroy();
						return null;
					}
				}
			}
			
		}else{ //only use existing modifiers, if order has been placed
			if($existingmodifiers){
				foreach($existingmodifiers as $modifier){
					$modifier->Sort = $sort;
					//TODO: prevent recalculating value if $this->Amount is present
						//this will help historical records to not be altered
					$runningtotal = $modifier->modify($runningtotal);
					$modifier->write();
				}
			}
		}
		$this->Total = $runningtotal;
		return $runningtotal;
	}
	
	/**
	 * Retrieve a modifier of a given class for this order.
	 * Modifier will be retrieved from database if it already exists, 
	 * or created if it is always required.
	 * 
	 * @param string $className
	 * @param boolean $forcecreate - force the modifier to be created.
	 */
	public function getModifier($className, $forcecreate = false){
		if(ClassInfo::exists($className)){
			//search for existing
			if($modifier = DataObject::get_one($className,"\"OrderID\" = ".$this->ID)){ //sort by?
				//remove if no longer valid
				if(!$modifier->valid()){
					//TODO: need to provide feedback message - why modifier was removed
					$modifier->delete();
					$modifier->destroy();
					return null;
				}
				return $modifier;
			}
			$modifier = new $className();
			if($modifier->required() || $forcecreate){ //create any modifiers that are required for every order
				$modifier->OrderID = $this->ID;
				$modifier->write();
				$this->Modifiers()->add($modifier);
				return $modifier;	
			}
		}else{
			user_error("Class \"$className\" does not exist.");
		}
		return null;
	}
	
	function GrandTotal(){
		if($this->Total){
			return $this->Total;
		}
		return $this->getField('Total');
	}
	
	function Total(){
		return $this->GrandTotal();
	}

	/**
	 * Checks to see if any payments have been made on this order
	 * and if so, subracts the payment amount from the order
	 * Precondition : The order is in DB
	 */
	function TotalOutstanding(){
		$total = $this->Total;
		$paid = $this->TotalPaid();
		$outstanding = $total - $paid;
		if(abs($outstanding) < self::$maximum_ignorable_sales_payments_difference) {
			return 0;
		}
		return $outstanding;
	}
	
	/**
	 * Add up successful payments
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
	 * Get the link for finishing order processing.
	 */
	function Link() {
		if($member = $this->Member()){
			return Controller::join_links(AccountPage::find_link(),'order',$this->ID);
		}
		return CheckoutPage::find_link(false,"finish",$this->ID);
	}

	/**
	 * Returns TRUE if the order can be cancelled
	 * PRECONDITION: Order is in the DB.
	 *
	 * @return boolean
	 */
	function canCancel() {
		switch($this->Status) {
			case 'Unpaid' : return self::$can_cancel_before_payment;
			case 'Paid' : return self::$can_cancel_before_processing;
			case 'Processing' : case 'Query' : return self::$can_cancel_before_sending;
			case 'Sent' : case 'Complete' : return self::$can_cancel_after_sending;
			default : return false;
		}
	}
	
	/**
	 * Check if an order can be paid for.
	 * 
	 * @return boolean
	 */
	public function canPay($member = null){
		if($this->TotalOutstanding() > 0){
			return true;
		}
		return false;
	}
	
	/*
	 * Prevent deleting orders.
	 * 
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return false;
	}
	
	/**
	 * Check if an order can be edited.
	 * 
	 * @return boolean
	 */
	public function canEdit($member = null) {
		return true;
	}

	/**
	 * Prevent standard creation of orders.
	 * 
	 * @return boolean
	 */
	public function canCreate($member = null) {
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

	/**
	 * Get the latest email for this order.
	 */
	function getLatestEmail(){
		if($this->MemberID && ($this->Member()->LastEdited > $this->LastEdited || !$this->Email)){
			return $this->Member()->Email;
		}
		return $this->getField('Email');
	}

	/**
	 * Gets the name of the customer.
	 */
	function getName(){
		$firstname = $this->Member()->FirstName;
		$surname = $this->Member()->Surname;
		return ($surname) ? trim($firstname . ' ' . $surname) : $firstname;
	}
	
	function getShippingAddress(){
		if($address = $this->ShippingAddress()){
			return $address;
		}elseif($this->Member() && $address = $this->Member()->DefaultShippingAddress()){
			return $address;
		}
		return null;
	}

	function getBillingAddress(){
		if(!$this->SeparateBillingAddress){
			return $this->getShippingAddress();
		}elseif($address = $this->BillingAddress()){
			return $address;
		}elseif($this->Member() && $address = $this->Member()->DefaultBillingAddress()){
			return $address;
		}
		return null;
	}
	
	function getFullShippingAddress(){
		return $this->getShippingAddress();
	}
	
	function getFullBillingAddress(){
		return $this->getBillingAddress();
	}

	// Order Template and ajax Management

	/**
	 * Will update payment status to "Paid if there is no outstanding amount".
	 */
	function updatePaymentStatus(){
		if($this->GrandTotal() > 0 && $this->TotalOutstanding() <= 0){
			//TODO: only run this if it is setting to Paid, and not cancelled or similar
			$this->Status = 'Paid';
			$this->write();
		}
	}

	/**
	 * Has this order been sent to the customer?
	 * (at "Sent" status).
	 *
	 * @return boolean
	 */
	function IsSent() {
		return $this->Status == 'Sent';
	}

	/**
	 * Is this order currently being processed?
	 * (at "Sent" OR "Processing" status).
	 *
	 * @return boolean
	 */
	function IsProcessing() {
		return $this->IsSent() || $this->Status == 'Processing';
	}

	/**
	 * Return whether this Order has been paid for (Status == Paid)
	 * or Status == Processing, where it's been paid for, but is
	 * currently in a processing state.
	 *
	 * @return boolean
	 */
	function IsPaid() {
		return $this->IsProcessing() || $this->Status == 'Paid';
	}

	function IsCart(){
		return $this->Status == 'Cart';
	}
	
	/**
	 * Create a unique reference identifier string for this order.
	 */
	function generateReference(){
		$reference = str_pad($this->ID,self::$reference_id_padding,'0',STR_PAD_LEFT);
		$this->extend('generateReference',$reference);
		$this->Reference = $reference;
		//prevent generating references that are the same
		$count = 0;
		while(DataObject::get_one('Order',"\"Reference\" = '$this->Reference'")){
			$count++;
			$this->Reference = $reference."".$count;
		}
	}
	
	/**
	 * Get the reference for this order
	 */
	function getReference(){
		return $this->getField('Reference') ? $this->getField('Reference') : $this->ID;
	}
	
	/**
	 * Return a link to the {@link CheckoutPage} instance
	 * that exists in the database.
	 *
	 * @return string
	 */
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
		if(!$this->isInDB()) {
			$country = ShoppingCart::has_country() ? ShoppingCart::get_country() : ShopMember::find_country();
		}
		elseif(!$this->UseShippingAddress || !$country = $this->ShippingCountry) {
			$country = ShopMember::find_country();
		}
		return $codeOnly ? $country : ShopMember::find_country_title($country);
	}
	
	/**
	 * Force creating an order reference
	 */
	function onBeforeWrite(){
		parent::onBeforeWrite();
		if(in_array($this->Status,self::$placed_status)){
			$this->generateReference();
		}
	}

	/**
	 * delete attributes, statuslogs, and payments
	 */
	function onBeforeDelete(){
		$this->Items()->removeAll();
		$this->Modifiers()->removeAll();
		$this->OrderStatusLogs()->removeAll();
		$this->Payments()->removeAll();
		parent::onBeforeDelete();
	}

	function debug(){
		$val = "<div class='order'><h1>$this->class</h1>\n<ul>\n";
		if($this->record) foreach($this->record as $fieldName => $fieldVal) {
			$val .= "\t<li>$fieldName: " . Debug::text($fieldVal) . "</li>\n";
		}
		$val .= "</ul>\n";
		
		$val .= "<div class='items'><h2>Items</h2>";
		if($items = $this->Items()){
			$val .= $this->Items()->debug();
			$val .= "<ul>";
			foreach($items as $item) { //extra debug info for items, since ComponentSet doesn't provdide this
				$val .= "<li style=\"list-style-type: disc; margin-left: 20px\">" . Debug::text($item) . "</li>";
			}
			$val .= "</ul>";
		}
		$val .= "</div><div class='modifiers'><h2>Modifiers</h2>";
		if($modifiers = $this->Modifiers()){
			$val .= $modifiers->debug();
			$val .= "<ul>";
			foreach($modifiers as $item) { //extra debug info for items, since ComponentSet doesn't provdide this
				$val .= "<li style=\"list-style-type: disc; margin-left: 20px\">" . Debug::text($item) . "</li>";
			}
			$val .= "</ul>";
		}
		$val .= "</div></div>";
			
		return $val;
	}
	
	//deprecated code
	
	/**
	* @deprecated Use OrderProcessor
	*/
	public static function set_email($email) {
		OrderProcessor::set_email_from($email);
	}
	/**
	 * @deprecated Use OrderProcessor
	 */
	public static function set_receipt_subject($subject) {
		OrderProcessor::set_receipt_subject($subject);
	}
	/**
	 * @deprecated Use OrderProcessor
	 */
	public static function set_subject($subject){
		OrderProcessor::set_receipt_subject($subject);
	}
	/**
	* @deprecated use OrderProcessor
	*/
	function sendReceipt() {
		OrderProcessor::create($this)->sendReceipt();
	}
	/**
	 * @deprecated use OrderProcessor
	 */
	function sendStatusChange($title, $note = null) {
		OrderProcessor::create($this)->sendStatusChange($title,$note);
	}

	/**
	* @deprecated use OrderProcessor placeOrder
	*/
	function save() {
		OrderProcessor::create($this)->placeOrder();
	}
	
	/**
	* Returns the subtotal of the modifiers for this order.
	* If a modifier appears in the excludedModifiers array, it is not counted.
	*
	* @param $excluded string|array Class(es) of modifier(s) to ignore in the calculation.
	* @todo figure out what the return type is? double? float?
	* @deprecated CreateModifiers will pass in subtotal
	*/
	function ModifiersSubTotal() {
		return $this->modifiertotal;
	}
	/**
	 * Initialise all the {@link OrderModifier} objects.
	 * @deprecated use Calculate function instead.
	 */
	function initModifiers() {
		$this->calculate();
	}
	/**
	* @deprecated use OrderProcessor
	*/
	protected function sendEmail($emailClass, $copyToAdmin = true) {
		OrderProcessor::create($this)->sendStatusChange($emailClass,$copyToAdmin);
	}

}
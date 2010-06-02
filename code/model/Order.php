<?php 
/** 
 * The order class is a databound object for handling Orders
 * within SilverStripe.
 * 
 * @package ecommerce
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
		'Status' => "Enum('Unpaid,Query,Paid,Processing,Sent,Complete,AdminCancelled,MemberCancelled','Unpaid')",
		'Country' => 'Text',
		'UseShippingAddress' => 'Boolean',
		'ShippingName' => 'Text',
		'ShippingAddress' => 'Text',
		'ShippingAddress2' => 'Text',
		'ShippingCity' => 'Text',
		'ShippingCountry' => 'Text',
		'Printed' => 'Boolean'
	);
	
	public static $has_one = array(
		'Member' => 'Member'
	);
	
	public static $has_many = array(
		'Attributes' => 'OrderAttribute',
		'OrderStatusLogs' => 'OrderStatusLog',
		'Payments' => 'Payment'
	);
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	public static $defaults = array();
	
	public static $casting = array(
		'SubTotal' => 'Currency',
		'Total' => 'Currency',
		'Shipping' => 'Currency',
		'TotalOutstanding' => 'Currency'
	);
	
	/**
	 * Any order with one of these values for the Status
	 * field indicates that the customer has paid for their order.
	 * 
	 * @var array
	 */
	static $paid_status = array('Paid', 'Processing', 'Sent', 'Complete');
	
	/**
	 * This is the from address that the receipt
	 * email contains. e.g. "info@shopname.com"
	 *
	 * @var string
	 */
	protected static $receipt_email;	

	/**
	 * This is the subject that the receipt
	 * email will contain. e.g. "Joe's Shop Receipt".
	 *
	 * @var string
	 */
	protected static $receipt_subject;

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
		'Member.FirstName' => 'Customer First Name',
		'Member.Surname' => 'Customer Surname',
		'Total' => 'Order Total',
		'Status' => 'Status'
	);
	
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
	 * Set the from address for receipt emails.
	 * 
	 * @param string $email From address. e.g. "info@myshop.com"
	 */
	public static function set_email($email) {
		self::$receipt_email = $email;
	}

	/**
	 * Set the subject of the order receipt email.
	 * 
	 * @param string $subject The subject line text
	 */
	public static function set_subject($subject) {
		self::$receipt_subject = $subject;
	}	
	
	/**
	 * Set the modifiers that apply to this site.
	 *
	 * @param array $modifiers An array of {@link OrderModifier} subclass names
	 */
	public static function set_modifiers($modifiers) {
		self::$modifiers = $modifiers;
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
	
	/**
	 * Initialise all the {@link OrderModifier} objects
	 * by evaluating init_for_order() on each of them.
	 */
	public static function init_all_modifiers() {
		if(self::$modifiers && is_array(self::$modifiers) && count(self::$modifiers) > 0) {
			foreach(self::$modifiers as $className) {
				if(class_exists($className)) {
					$modifier = new $className();
					if($modifier instanceof OrderModifier) eval("$className::init_for_order(\$className);");
				}
			}
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
	 * Save the current order, writing it to
	 * the database.
	 *
	 * @return Order The current order
	 */
	public static function save_current_order() {
		
		// Create a new order, and write it
		$order = new Order();
		$order->write();
		
		// Set the items from the cart into the order
		if($items = ShoppingCart::get_items()) $order->createItems($items, true);
		
		// Set the modifiers from the cart into the order
		if($modifiers = ShoppingCart::get_modifiers()) $order->createModifiers($modifiers, true);
		
		// Set the Member relation to this order
		$order->MemberID = Member::currentUserID();
		
		// Write the order
		$order->write();
		
		return $order;
	}	
	
	// Items Management
	
	/**
	 * Returns the items of the order, if it hasn't been saved yet
	 * it returns the items from session, if it has, it returns them 
	 * from the DB entry.
	 */
	function Items() {
 		if($this->ID) return $this->itemsFromDatabase();
 		elseif($items = ShoppingCart::get_items()) return $this->createItems($items);
	}
	
	/**
	 * Return all the {@link OrderItem} instances that are
	 * available as records in the database.
	 *
	 * @return DataObjectSet
	 */
	protected function itemsFromDatabase() {
		return DataObject::get('OrderItem', "`OrderID` = '$this->ID'");
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
	 * Returns the subtotal of the items for this order.
	 */
	function _SubTotal() {
		$result = 0;
		if($items = $this->Items()) {
			foreach($items as $item) $result += $item->Total();
		}
		return $result;
	}
	
	/**
	 * Returns the modifiers of the order, if it hasn't been saved yet
	 * it returns the modifiers from session, if it has, it returns them 
	 * from the DB entry.
	 */ 
 	function Modifiers() {
 		if($this->ID) {
 			return $this->modifiersFromDatabase();
 		} elseif($modifiers = ShoppingCart::get_modifiers()) {
 			return $this->createModifiers($modifiers);
 		} else {
 			return false;
 		}
	}
	
	/**
	 * Get all {@link OrderModifier} instances that are
	 * available as records in the database.
	 *
	 * @return DataObjectSet
	 */
	protected function modifiersFromDatabase() {
		return DataObject::get('OrderModifier', "`OrderID` = '$this->ID'");
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
	function _ModifiersSubTotal($excluded = null) {
		$total = 0;
		
		if($modifiers = $this->Modifiers()) {
			foreach($modifiers as $modifier) {
				if(is_array($excluded) && in_array($modifier->class, $excluded)) {
					continue;
				} elseif($excluded && ($modifier->class == $excluded)) {
					continue;
				}
				
				$total += $modifier->Total();
			}
		}
		
		return $total;
	}
	
	// Order Management
	
	/**
  	 * Returns the total cost of an order including the additional charges or deductions of its modifiers.
  	 */
	function _Total() {
		return $this->_SubTotal() + $this->_ModifiersSubTotal();
	}
	
	/**
	 * Checks to see if any payments have been made on this order
	 * and if so, subracts the payment amount from the order
	 * Precondition : The order is in DB
	 */
	function _TotalOutstanding(){
		$total = $this->_Total();
		if($payments = $this->Payments()) {
			foreach($payments as $payment) {
				if($payment->Status == 'Success') $total -= $payment->Amount;
			}
		}
		return $total;
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
		switch($this->Status) {
			case 'Unpaid' : return self::$can_cancel_before_payment;
			case 'Paid' : return self::$can_cancel_before_processing;
			case 'Processing' : case 'Query' : return self::$can_cancel_before_sending;
			case 'Sent' : case 'Complete' : return self::$can_cancel_after_sending;
			default : return false;
		}
	}
		
	/**
	 * Returns the {@link Payment} records linked
	 * to this order.
	 * 
	 * PRECONDITION: Order is in DB.
	 * 
	 * @return DataObjectSet
	 */
	function Payments() {
		return DataObject::get('Payment', "OrderID = '$this->ID'", 'LastEdited DESC');
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
	
	// Order Template Management
	
	function TableSubTotalID() {
		return 'Table_Order_SubTotal';
	}

	function TableTotalID() {
		return 'Table_Order_Total';
	}
	
	function CartSubTotalID() {
		return 'Cart_Order_SubTotal';
	}

	function CartTotalID() {
		return 'Cart_Order_Total';
	}
	
	function updateForAjax(array &$js) {
		$subTotal = DBField::create('Currency', $this->_SubTotal())->Nice();
		$total = DBField::create('Currency', $this->_Total())->Nice() . ' ' . Payment::site_currency();
		$js[] = array('id' => $this->TableSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
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
	
	/**
	 * Return a string of localised text based on the
	 * determination of whether this order is paid for,
	 * or not, by checking {@link IsPaid()}.
	 *
	 * @return string
	 */
	function Status() {
		return $this->IsPaid() ? _t('Order.SUCCESSFULL', 'Order Successful') : _t('Order.INCOMPLETE', 'Order Incomplete');
	}
	
	/**
	 * Return a link to the {@link CheckoutPage} instance
	 * that exists in the database.
	 *
	 * @return string
	 */
	function checkoutLink() {
		return $this->ID ? CheckoutPage::get_checkout_order_link($this->ID) : CheckoutPage::find_link();
	}
	
  	/**
	 * Send the receipt of the order by mail.
	 * Precondition: The order payment has been successful
	 */
	function sendReceipt() {
		$this->sendEmail('Order_ReceiptEmail');
	}
  	
	/**
	 * Send a mail of the order to the client (and another to the admin).
	 * 
	 * @param $emailClass - the class name of the email you wish to send
	 * @param $copyToAdmin - true by default, whether it should send a copy to the admin
	 */
	protected function sendEmail($emailClass, $copyToAdmin = true) {
 		$from = self::$receipt_email ? self::$receipt_email : Email::getAdminEmail();
 		$to = $this->Member()->Email;
		$subject = self::$receipt_subject ? self::$receipt_subject : "Shop Sale Information #$this->ID";
 		
 		$purchaseCompleteMessage = DataObject::get_one('CheckoutPage')->PurchaseComplete;
 		
 		$email = new $emailClass();
 		$email->setFrom($from);
 		$email->setTo($to);
 		$email->setSubject($subject);
		if($copyToAdmin) $email->setBcc(Email::getAdminEmail());
		
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
			$country = ShoppingCart::has_country() ? ShoppingCart::get_country() : EcommerceRole::findCountry();
		} elseif(!$this->UseShippingAddress || !$country = $this->ShippingCountry) {
			$country = EcommerceRole::findCountry();
		}
		
		return $codeOnly ? $country : EcommerceRole::findCountryTitle($country);
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
	function sendStatusChange($note = null) {
		if(!$note) {
			$logs = DataObject::get('OrderStatusLog', "OrderID = {$this->ID}", "Created DESC", null, 1);
			$latestLog = $logs->First();
			$note = $latestLog->Note;
		}
		
		$member = $this->Member();
		
 		if(self::$receipt_email) {
 			$adminEmail = self::$receipt_email;
 		} else {
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
		$e->setSubject('Your order status');
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
		
		$exist = DB::query("SHOW COLUMNS FROM `Order` LIKE 'Shipping'")->numRecords();
 		if($exist > 0) {
 			if($orders = DataObject::get('Order')) {
 				foreach($orders as $order) {
 					$id = $order->ID;
 					$hasShippingCost = DB::query("SELECT `hasShippingCost` FROM `Order` WHERE `ID` = '$id'")->value();
 					$shipping = DB::query("SELECT `Shipping` FROM `Order` WHERE `ID` = '$id'")->value();
 					$addedTax = DB::query("SELECT `AddedTax` FROM `Order` WHERE `ID` = '$id'")->value();
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
 				Database::alteration_message('The \'SimpleShippingModifier\' and \'TaxModifier\' objects have been successfully created and linked to the appropriate orders present in the \'Order\' table', 'created');	
 			}
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `hasShippingCost` `_obsolete_hasShippingCost` tinyint(1)");
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `Shipping` `_obsolete_Shipping` decimal(9,2)");
 			DB::query("ALTER TABLE `Order` CHANGE COLUMN `AddedTax` `_obsolete_AddedTax` decimal(9,2)");
 			Database::alteration_message('The columns \'hasShippingCost\', \'Shipping\' and \'AddedTax\' of the table \'Order\' have been renamed successfully. Also, the columns have been renamed respectly to \'_obsolete_hasShippingCost\', \'_obsolete_Shipping\' and \'_obsolete_AddedTax\'', 'obsolete');
  		}
  		
  		// 2) Cancel status update
  		
  		if($orders = DataObject::get('Order', "`Status` = 'Cancelled'")) {
  			foreach($orders as $order) {
  				$order->Status = 'AdminCancelled';
  				$order->write();
  			}
  			Database::alteration_message('The orders which status was \'Cancelled\' have been successfully changed to the status \'AdminCancelled\'', 'changed');
  		}
	}
	
}

/**
 * This class handles the receipt email which gets sent once an order is made.
 * You can call it by issuing sendReceipt() in the Order class.
 */  
class Order_ReceiptEmail extends Email {

	protected $ss_template = 'Order_ReceiptEmail';

}

/**
 * This class handles the status email which is sent after changing the attributes
 * in the report (eg. status changed to 'Shipped').
 */ 
class Order_StatusEmail extends Email {

	protected $ss_template = 'Order_StatusEmail';

}

class Order_CancelForm extends Form {
	
	function __construct($controller, $name, $orderID) {
		
		$fields = new FieldSet(
			new HiddenField('OrderID', '', $orderID)
		);
		
		$actions = new FieldSet(
			new FormAction('doCancel', 'Cancel Order')
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
		$SQL_data = Convert::raw2sql($data);
		
		$order = DataObject::get_by_id('Order', $SQL_data['OrderID']);
		$order->Status = 'MemberCancelled';
		$order->write();
		
		Director::redirectBack();
		return;
	}
	
}
?>
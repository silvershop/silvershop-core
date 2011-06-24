<?php

/**
 * which returns an array of IDs
 * SEQUENCE - USE FOR ALL MODIFIERS!!!
 * *** model defining static variables (e.g. $db, $has_one)
 * *** cms variables + functions (e.g. getCMSFields, $searchableFields)
 * *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)
 * *** CRUD functions (e.g. canEdit)
 * *** init and update functions
 * *** form functions (e. g. showform and getform)
 * *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES
 * ***  inner calculations.... USES CALCULATED VALUES
 * *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES
 * *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
 * *** AJAX related functions
 * *** debug functions
 *
 * FAQs
 *
 * *** What is the difference between cart and table ***
 * The Cart is a smaller version of the Table. Table is used for Checkout Page + Confirmation page.
 * Cart is used for other pages (pre-checkout for example). At times, the values and names may differ
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: modifiers
 *
 **/
class OrderModifier extends OrderAttribute {

// ########################################  *** model defining static variables (e.g. $db, $has_one)
	public static $db = array(
		'Name' => 'Varchar(255)', // we use this to create the TableTitle, CartTitle and TableSubTitle
		'TableValue' => 'Currency', //the $$ shown in the checkout table
		'CalculationValue' => 'Currency', // this is the value we use to deduct / add to the sub-total (e.g. discount = -20, delivery = +10)
		'HasBeenRemoved' => 'Boolean' // we add this so that we can see what modifiers have been removed
	);

	// make sure to choose the right Type and Name for this.
	public static $defaults = array(
		'Name' => 'Modifier' //making sure that you choose a different name for any class extensions.
	);

// ########################################  *** cms variables  + functions (e.g. getCMSFields, $searchableFields)

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"TableTitle" => "PartialMatchFilter",
		"TableValue",
		"HasBeenRemoved"
	);

	public static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Table Title",
		"TableValue" => "Value Shown"
	);

	public static $casting = array(
		'CartValue' => 'Currency', // the $$ shown in the cart (smaller version of the checkout table)
	);

	public static $singular_name = "Order Extra";
		function i18n_singular_name() { return _t("OrderModifier.ORDERMODIFIER", "Order Extra");}

	public static $plural_name = "Order Extras";
		function i18n_plural_name() { return _t("OrderModifier.ORDERMODIFIERS", "Order Extras");}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		return $fields;
	}

	/**
	 *
	 * @return FieldSet
	  **/
	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

// ########################################  *** other static variables (e.g. special_name_for_something)

	/**
	 * $do_not_add_automatically Identifies whether a modifier is NOT automatically added
	 * Most modifiers, such as delivery and GST would be added automatically.
	 * However, there are also ones that are not added automatically.
	 * @var Boolean
	 **/
	protected static $do_not_add_automatically = false;
		static function set_do_not_add_automatically($b) {self::$do_not_add_automatically = $b;}
		static function get_do_not_add_automatically() {return self::$do_not_add_automatically;}

	/**
	 * $can_be_removed Identifies whether a modifier can be removed by the user.
	 * @var Boolean
	 **/
	protected static $can_be_removed = false;
		static function set_can_be_removed($b) {self::$can_be_removed = $b;}
		static function get_can_be_removed() {return $this->stat("can_be_removed");}

	/**
	 * we use this variable to make sure that the parent::runUpdate() is called in all child classes
	 * this is similar to the checks run for parent::init in the controller class.
	 * @var Boolean
	 **/
	protected $baseInitCalled = false;

	/**
	* This is a flag for running an update.
	* Running an update means that all fields are (re)set, using the Live{FieldName} methods.
	* @var Boolean
	**/
	protected $mustUpdate = false;


// ######################################## *** CRUD functions (e.g. canEdit)



// ########################################  *** init and update functions


	public static function init_for_order($className) {
		user_error("this function has been depreciated, instead, use $myModifier->init()", E_USER_ERROR);
		return false;
	}

	/**
	* This method runs when the OrderModifier is first added to the order.
	**/
	public function init() {
		parent::init();
		$this->write();
		$this->mustUpdate = true;
		$this->runUpdate();
		return true;
	}

	/**
	* all modifier child-classes must have this method if it has more fields
	*
	**/
	public function runUpdate() {
		if(!$this->IsRemoved()) {
			$this->checkField("CalculationValue");			
			$this->checkField("Name");
			$this->checkField("TableValue");
			$this->checkField("CartValue");
			if($this->mustUpdate && $this->canBeUpdated()) {
				$this->write();
			}
		}
		$this->baseInitCalled = true;
	}

	/**
	* You can overload this method as canEdit might not be the right indicator.
	* @return Boolean
	**/
	protected function canBeUpdated() {
		return $this->canEdit();
	}

	/**
	* This method simply checks if a fields has changed and if it has changed it updates the field.
	**/
	protected function checkField($fieldName) {
		//$start =  microtime();
		if($this->canBeUpdated()) {
			$functionName = "Live".$fieldName;
			if($this->$functionName() != $this->$fieldName) {
				$this->$fieldName = $this->$functionName();
				$this->mustUpdate = true;
			}
		}
		//debug::show($this->ClassName.".".$fieldName.": ".floatval(microtime() - $start));
	}

	/**
	 * Provides a modifier total that is positive or negative, depending on whether the modifier is chargable or not.
	 * This number is used to work out the order Grand Total.....
	 * It is important to note that this can be positive or negative, while the amount is always positive.
	 * @return float / double
	 */
	public function CalculationTotal() {
		if($this->HasBeenRemoved) {
			return 0;
		}
		return $this->CalculationValue;
	}

// ########################################  *** form functions (showform and getform)

	/**
	 * This determines whether the OrderModifierForm is shown or not. {@link OrderModifier::get_form()}.
	 * OrderModifierForms are forms that are added to check out to facilitate the use of the modifier.
	 * An example would be a form allowing the user to select the delivery option.
	 * @return boolean
	 */
	public function showForm() {
		return false;
	}

	/**
	 * This function returns a form that allows a user
	 * to change the modifier to the order.
	 *
	 *
	 * @param String $name- name for the modifier form
	 * @param Controller $optionalController  - optional custom controller class
	 * @param Validator $optionalValidator  - optional custom validator class
	 * @return OrderModifierForm or subclass
	 */
	public function getModifierForm($name = 'ModifierForm', $optionalController = null, $optionalValidator = null) {
		if($this->showForm()) {
			return new OrderModifierForm($optionalController, $name, $fields = new FieldSet(), $actions = new FieldSet(), $optionalValidator);
		}
	}




// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...)

	/**
	* tells you whether the modifier shows up on the checkout  / cart form.
	* this is also the place where we check if the modifier has been updated.
	*@return Boolean
	**/
	public function ShowInTable() {
		if(!$this->baseInitCalled && $this->canBeUpdated()) {
			user_error("While the order can be edited, you must call the runUpdate method everytime you get the details for this modifier", E_USER_ERROR);
		}
		return false;
	}

	/**
	* some modifiers can be hidden after an ajax update (e.g. if someone enters a discount coupon and it does not exist).
	* There might be instances where ShowInTable (the starting point) is TRUE and HideInAjaxUpdate return false.
	*@return Boolean
	**/
	public function HideInAjaxUpdate() {
		if($this->ShowInTable()) {
			return false;
		}
		return true;
	}

	/**
	 * Checks if the modifier can be removed.
	 *
	 * @return boolean
	 **/
	public function CanBeRemoved() {
		return self::get_can_be_removed();
	}

	/**
	 * Checks if the modifier can be added manually
	 *
	 * @return boolean
	 **/
	public function CanAdd() {
		return $this->HasBeenRemoved || $this->DoNotAddOnInit();
	}

	/**
	 *Identifier whether a modifier will be added automatically for all new orders.
	 * @return boolean
	 */
	public function DoNotAddAutomatically() {
		return self::get_do_not_add_automatically();
	}


	/**
	 * This is what shows up on the actual cart / checkout page
	 *
	 * @return Currency Object
	 **/
	public function TableValue() {
		$amount = $this->CalculationValue;
		$obj = DBField::create('Currency', $amount);
		return $obj;
	}


	/**
	 * Sometimes we need a difference between Cart and Checkout Value - the cart value can be differentiated here.
	 *
	 * @return Currency Object
	 **/
	public function CartValue() {
		return $this->TableValue();
	}
	/**
	 * Actual calculation used
	 *
	 * @return Float / Double
	 **/
	public function CalculationValue() {
		return $this->CalculationValue;
	}

	/**
	 * This describes what the name of the  modifier should be, in relation to
	 * the order table on the check out page - which the templates uses directly.
	 * For example, this could be something  like "Shipping to NZ", where NZ is a
	 * dynamic variable on where the user currently is, using {@link Geoip}.
	 *
	 * @return string
	 */
	public function TableTitle() {
		return $this->Name;
	}

	/**
	 * Sometimes we need a difference between Cart and Checkout Title - the cart Title can be differentiated here.
	 *
	 * @return String
	 **/

	public function CartTitle() {
		return $this->TableTitle();
	}

	/**
	 * This link is for modifiers that have been removed and are being put "back".
	 * @return String
	  **/
	public function AddLink() {
		return ShoppingCart::remove_modifier_link($this->ID,$this->ClassName);
	}
	/**
	 *
	 * @return String
	  **/
	public function RemoveLink() {
		return ShoppingCart::remove_modifier_link($this->ID,$this->ClassName);
	}


// ######################################## ***  inner calculations....


// ######################################## ***  calculate database fields ( = protected function Live[field name]() { ....}

	protected function LiveName() {
		return self::$defaults["Name"];
	}

	protected function LiveTableValue() {
		return $this->LiveCalculationValue();
	}

	protected function LiveCartValue() {
		return $this->LiveCalculationValue();
	}
	/**
	 * This function is always called to determine the
	 * amount this modifier needs to charge or deduct - if any.
	 *
	 *
	 * @return Currency
	 */
	protected function LiveCalculationValue() {
		return $this->CalculationValue;
	}


// ######################################## ***  Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

	/**
	 * should be extended if it is true in child class
	 * @return boolean
	 */
	public function IsChargeable() {
		return $this->LiveCalculationValue() > 0;
	}
	/**
	 * should be extended if it is true in child class
	 * @return boolean
	 */
	public function IsDeductable() {
		return $this->LiveCalculationValue() < 0;
	}

	/**
	 * should be extended if it is true in child class
	 * @return boolean
	 */
	public function IsNoChange() {
		return $this->LiveCalculationValue()  == 0 ;
	}

	/**
	 * should be extended if it is true in child class
	 * Needs to be a public class
	 * @return boolean
	 */
	public function IsRemoved() {
		return $this->HasBeenRemoved;
	}



// ######################################## ***  standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	/**
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
	}



// ######################################## ***  AJAX related functions

	/**
	 *
	 *
	 *return $array for AJAX JSON
	 **/
	function updateForAjax(array &$js) {
		$tableValue = DBField::create('Currency',$this->TableValue())->Nice();
		$cartValue = DBField::create('Currency',$this->CartValue())->Nice();
		if($this->HideInAjaxUpdate()) {
			$js[] = array("id" => $this->TableID(), 'parameter' => "hide", "value" => 1);
		}
		else {
			$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $tableValue);
			$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $cartValue);
			$js[] = array('id' => $this->TableTitleID(), 'parameter' => 'innerHTML', 'value' => $this->TableTitle());
			$js[] = array('id' => $this->CartTitleID(), 'parameter' => 'innerHTML', 'value' => $this->CartTitle());
			$js[] = array("id" => $this->TableID(), 'parameter' => "hide", "value" => $this->HideInAjaxUpdate());
		}
	}


// ######################################## ***  debug functions

	/**
	 * Debug helper method.
	 */
	public function debug() {
		return "
			<h2>".$this->ClassName."</h2>
			<h3>OrderModifier class details</h3>
			<p>
				<b>ID : </b>".$this->ID."<br/>
				<b>Order ID : </b>".$this->OrderID."<br/>
				<b>Calculation Value : </b>".$this->CalculationValue()."<br/>
				<b>Table Title: </b>".$this->TableTitle()."<br/>
				<b>Table Value: </b>".$this->TableValue()."<br/>
				<b>Cart Value: </b>".$this->CartTitle()."<br/>
				<b>Cart Title: </b>".$this->CartValue()."<br/>
			</p>";
	}

}



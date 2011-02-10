<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_modifiers
 * @description: shows a list of recommended products
 * the product page / dataobject need to have a function RecommendedProductsForCart
 * which returns an array of IDs
 * SEQUENCE - USE FOR ALL MODIFIERS!!!
// ######################################## *** model defining static variables (e.g. $db, $has_one)
// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)
// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)
// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions
// ######################################## *** form functions (e. g. showform and getform)
// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES
// ######################################## ***  inner calculations.... USES CALCULATED VALUES
// ######################################## *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES
// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)
// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
// ######################################## *** AJAX related functions
// ######################################## *** debug functions
 */
class OrderModifier extends OrderAttribute {



// ########################################  *** model defining static variables (e.g. $db, $has_one)
	public static $db = array(
		'Name' => 'Varchar(255)',
		'Amount' => 'Currency',
		'Type' => "Enum('Chargeable,Deductable,NoChange,Removed')"
	);

	public static $casting = array(
		'TableValue' => 'Currency',
		'CartValue' => 'Currency',
		'CalculationTotal' => 'Currency'
	);

	public static $defaults = array(
		'Type' => 'Chargeable',
		'Name' => 'Modifier'
	);



// ########################################  *** cms variables  + functions (e.g. getCMSFields, $searchableFields)

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"Title" => "PartialMatchFilter",
		"TableTitle" => "PartialMatchFilter",
		"CartTitle" => "PartialMatchFilter",
		"Amount",
		"Type"
	);

	public static $summary_fields = array(
		"Order.ID" => "Order ID",
		"TableTitle" => "Table Title",
		"ClassName" => "Name",
		"Amount" => "Amount" ,
		"Type" => "Type"
	);

	public static $singular_name = "Order Extra";
		function i18n_singular_name() { return _t("OrderModifier.ORDERMODIFIER", "Order Extra");}

	public static $plural_name = "Order Extras";
		function i18n_plural_name() { return _t("OrderModifier.ORDERMODIFIERS", "Order Extras");}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		return $fields;
	}

	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}



// ########################################  *** other static variables (e.g. special_name_for_something)

	protected $baseInitCalled = false;

	protected $mustUpdate = false;

	protected $_canEdit = null;


// ######################################## *** CRUD functions (e.g. canEdit)



// ########################################  *** init and update functions


	public static function init_for_order($className) {
		user_error("this function has been depreciated, instead, use $myModifier->init()", E_USER_ERROR);
		return false;
	}

	public function init() {
		parent::init();
		return true;
	}

	/**
	* each modifier class must have this function, at least if it has more dataobjects!
	*@param $mustUpdate Boolean, passed on from Child Class....
	*
	**/

	public function runUpdate() {
		$this->checkField("Name");
		$this->checkField("Amount");
		$this->checkField("Type");
		$this->checkField("Type");
		if($this->mustUpdate) {
			$this->write();
		}
		$this->baseInitCalled = true;
	}

	protected function checkField($fieldName) {
		if($this->_canEdit === null) {
			$this->_canEdit = $this->canEdit();
		}
		if($this->_canEdit) {
			$functionName = "Live".$fieldName;
			if($this->$functionName() != $this->$fieldName) {
				$this->$fieldName = $this->$functionName();
				$this->mustUpdate = true;
			}
		}
	}

	/**
	 * Provides a modifier total that is positive or negative, depending on whether the modifier is chargable or not.
	 * This number is used to work out the order Grand Total.....
	 * @return float / double
	 */
	public function CalculationTotal() {
		if($this->IsRemoved()) {
			return 0;
		}
		else {
			$amount = $this->Amount;
			if($this->IsChargeable()) {
				return $amount;
			}
			elseif($this->IsDeductable()) {
 				return -1 * $amount;
			}
			elseif($this->IsNoChange()) {
				return 0;
			}
			else {
				user_error("could not work out value for ".$this->Name, E_USER_WARNING);
				return 0;
			}
		}
	}

// ########################################  *** form functions (showform and getform)

	/**
	 * This determines whether the OrderModifierForm
	 * is shown or not. {@link OrderModifier::get_form()}.
	 *
	 * @return boolean
	 */
	public function showForm() {
		/* TO DO: find a better place for it...
		if(!$this->baseInitCalled) {
			user_error("While the order can be edited, you must call the init method everytime you get the details for this modifier", E_USER_ERROR);
		}
		*/
		return false;
	}

	/**
	 * This function returns a form that allows a user
	 * to change the modifier to the order.
	 *
	 * @todo When is this used?
	 * @todo How is this used?
	 * @todo How does one create their own OrderModifierForm implementation?
	 *
	 * @param Controller $controller $controller The controller
	 * @return OrderModifierForm or subclass
	 */
	public function getForm($controller) {
		return new OrderModifierForm($controller, 'ModifierForm', new FieldSet(), new FieldSet());
	}




// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...)

	public function ShowInTable() {
		if(!$this->baseInitCalled && $this->canEdit()) {
			user_error("While the order can be edited, you must call the init method everytime you get the details for this modifier", E_USER_ERROR);
		}
		return false;
	}


	/**
	 * Checks if the modifier can be removed. Default check is for whether it is Chargeable.
	 *
	 * @return boolean
	 **/
	public function CanRemove() {
		return !$this->IsChargeable();
	}

	/**
	 * Checks if the modifier can be added.
	 *
	 * @return boolean
	 **/
	public function CanAdd() {
		return $this->IsRemoved();
	}

	public function TableValue() {
		if($this->Type == "Chargeable") {
			$amount = $this->Amount;
		}
		elseif($this->Type == "Deductable") {
		 $amount = -1 * $this->Amount; //TODO: this is different from the bracket syntax for displaying negatives
		}
		else {
			$amount = 0;
		}
		$obj = DBField::create('Currency', $amount);
		return $obj;
	}


	public function CartValue() {
		return $this->TableValue();
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

	public function CartTitle() {
		return $this->TableTitle();
	}

	public function RemoveLink() {
		return ShoppingCart::remove_modifier_link($this->ID);
	}




// ######################################## ***  inner calculations....


// ######################################## ***  calculate database fields ( = protected function Live[field name]() { ....}

	protected function LiveName() {
		return self::$defaults["Name"];
	}

	/**
	 * This function is always called to determine the
	 * amount this modifier needs to charge or deduct.
	 *
	 * If the modifier exists in the DB, in which case it
	 * already exists for a given order, we just return
	 * the Amount data field from the DB. This is for
	 * existing orders.
	 *
	 * @return Currency
	 */
	protected function LiveAmount() {
		if($this->Type == "Removed" || $this->Type == "NoChange") {
			return 0;
		}
		return $this->Amount;
	}

	/**
	 * Provides a modifier total that is positive or negative, depending on whether the modifier is chargable or not.
	 *
	 * @return String
	 */
	protected function LiveType() {
		$v = $this->Type;
		if(!$this->IsRemoved()) {
			if($this->IsNoChange()) {
				$v = "NoChange";
			}
			elseif($this->IsDeductable()) {
				$v = "Deductable";
			}
			elseif($this->IsChargeable()){
				$v = "Chargeable";
			}
			else {
				user_error("Could not work out modifier type (chargeable, Deductable, or NoChange", E_USER_WARNING);
			}
		}
		if(!$v) {
			$v = self::$defaults["Type"];
		}
		return $v;
	}




// ######################################## ***  Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

	/**
	 * should be extended if it is true in child class
	 * @return boolean
	 */
	protected function IsChargeable() {
		return false;
	}
	/**
	 * should be extended if it is true in child class
	 * @return boolean
	 */
	protected function IsDeductable() {
		return false;
	}

	/**
	 * should be extended if it is true in child class
	 * @return boolean
	 */
	protected function IsNoChange() {
		return false;
	}

	/**
	 * always the same, do not extend.
	 * @return boolean
	 */
	protected function IsRemoved() {
		return $this->Type == "Removed";
	}


// ######################################## ***  standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	/**
	 * Before this OrderModifier is written to the database, we set some of the fields
	 * based on the way it was set up
	 * Precondition: The order item is not saved in the database yet.
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
	}



// ######################################## ***  AJAX related functions

	function updateForAjax(array &$js) {
		$tableValue = DBField::create('Currency',$this->TableValue())->Nice();
		$cartValue = DBField::create('Currency',$this->CartValue())->Nice();
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $tableValue);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $cartValue);
		$js[] = array('id' => $this->TableTitleID(), 'parameter' => 'innerHTML', 'value' => $this->TableTitle());
		$js[] = array('id' => $this->CartTitleID(), 'parameter' => 'innerHTML', 'value' => $this->CartTitle());
	}




// ######################################## ***  debug functions

	/**
	 * Debug helper method.
	 */
	public function debug() {
		$amount = $this->Amount;
		$type = $this->Type;
		$orderID = $this->OrderID;
		return <<<HTML
			<h2>$this->class</h2>
			<h3>OrderModifier class details</h3>
			<p>
				<b>ID : </b>$id<br/>
				<b>Amount : </b>$amount<br/>
				<b>Type : </b>$type<br/>
				<b>Order ID : </b>$orderID
			</p>
HTML;
	}

}



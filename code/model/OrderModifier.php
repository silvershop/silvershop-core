<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_modifiers
 * @description: shows a list of recommended products
 * the product page / dataobject need to have a function RecommendedProductsForCart
 * which returns an array of IDs
 * SEQUENCE - USE FOR ALL MODIFIERS!!!
  *** model defining static variables (e.g. $db, $has_one)
  *** cms variables + functions (e.g. getCMSFields, $searchableFields)
  *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)
  *** CRUD functions (e.g. canEdit)
  *** init and update functions
  *** form functions (e. g. showform and getform)
  *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES
  ***  inner calculations.... USES CALCULATED VALUES
  *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES
  *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)
  *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
  *** AJAX related functions
  *** debug functions
 */
class OrderModifier extends OrderAttribute {



// ########################################  *** model defining static variables (e.g. $db, $has_one)
	public static $db = array(
		'Name' => 'Varchar(255)',
		'Amount' => 'Currency',
		'Type' => "Enum('Chargeable,Deductable,NoChange')" //this should go
	);

	public static $casting = array(
		'TableValue' => 'Currency',
		'CartValue' => 'Currency',
		'CalculationTotal' => 'Currency'
	);

	// make sure to choose the right Type and Name for this.
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

	/**
	* we use this variable to make sure that the parent::runUpdate() is called in all child classes
	* this is similar to parent::init in the controller.
	**/
	protected $baseInitCalled = false;

	/**
	* This is a flag for running an update.
	* Running an update means that all fields are (re)set, using the Live{FieldName} methods.
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
		$this->checkField("Name");
		$this->checkField("Amount");
		$this->checkField("Type");
		if($this->mustUpdate && $this->canBeUpdated()) {
			$this->write();
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
	 * OrderModifierForms are forms that are added to check out to facilitate the use of the modifier
	 * an example would be a form allowing the user to select the delivery option.
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
	 * Checks if the modifier can be removed. Default check is for whether it is Chargeable.
	 *
	 * @return boolean
	 **/
	public function CanRemove() {
		return !$this->IsChargeable();
	}

	/**
	 * Checks if the modifier can be added again after it has been removed.
	 *
	 * @return boolean
	 **/
	public function CanAdd() {
		return $this->IsRemoved();
	}

	/**
	 * This is what shows up on the actual cart / checkout page
	 *
	 * @return Currency Object
	 **/
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


	/**
	 * Sometimes we need a difference between Cart and Checkout Value - the cart value can be differentiated here.
	 *
	 * @return Currency Object
	 **/
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

	/**
	 * Sometimes we need a difference between Cart and Checkout Title - the cart Title can be differentiated here.
	 *
	 * @return Currency Object
	 **/

	public function CartTitle() {
		return $this->TableTitle();
	}

	public function RemoveLink() {
		return ShoppingCart::remove_modifier_link($this->ID,$this->ClassName);
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


/**
 * This controller allows you to submit modifier forms from anywhere on the site, especially the cart page.
 */
class OrderModifier_Controller extends Controller{
	
	static $allowed_actions = array(
		'removemodifier'
	);
	
	public function init() {
		$this->initVirtualMethods();
		parent::init();
	}
	
	/**
	 * Inits the virtual methods from the name of the modifier forms to
	 * redirect the action method to the form class
	 */
	protected function initVirtualMethods() {
		
		if($forms = Order::get_modifier_forms($this)) {
			foreach($forms as $form) {
				$this->addWrapperMethod($form->Name(), 'getOrderModifierForm');
				self::$allowed_actions[] = $form->Name(); // add all these forms to the list of allowed actions also
			}
		}
	}
	
	/**
	 * Return a specific {@link OrderModifierForm} by it's name.
	 *
	 * @param string $name The name of the form to return
	 * @return Form
	 */
	protected function getOrderModifierForm($name) {
		if($forms = Order::get_modifier_forms($this)) {
			foreach($forms as $form) {
				if($form->Name() == $name) return $form;
			}
		}
	}
	
	function Link($action = null){
		$action = ($action)? "/$action/" : ""; 
		return $this->class.$action;
	}
	
	function removemodifier(){
		//TODO: move from shopping cart
	}	
}



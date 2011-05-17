<?php


/**
 * @description: this class is the base class for modifier forms in the checkout form... we could do with more stuff here....
 *
 * @see OrderModifier
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: forms
 *
 **/

class OrderModifierForm extends Form {

	/**
	 * You can set your own controller class here which will handle the form submission.
	 * One is provided by default, but you can also choose "OrderModifierForm_AjaxSubmit" or your own one.
	 *
	 *@var String
	 **/
	protected static $controller_class = "OrderModifierForm_Controller";
		static function set_controller_class(String $s) {self::$controller_class = $s;}
		static function get_controller_class() {return self::$controller_class;}

	/**
	 * You can set your own validator class here which will handle the form submission.
	 *
	 *@var String
	 **/
	protected static $validator_class = "OrderModifierForm_Validator";
		static function set_validator_class(String $s) {self::$validator_class = $s;}
		static function get_validator_class() {return self::$validator_class;}

	protected $order;


	/**
	 *NOTE: we semi-enforce using the OrderModifier_Controller here to deal with the submission of the OrderModifierForm
	 * You can use your own modifiers or an extension of OrderModifier_Controller by setting the first parameter (optionalController)
	 * to your own controller.
	 *
	 *@param $optionalController Controller
	 *@param $name String
	 *@param $fields FieldSet
	 *@param $actions FieldSet
	 *@param $validator SS_Validator
	 **/

	function __construct($optionalController = null, $name,FieldSet $fields, FieldSet $actions,$validator = null){
		if(!$optionalController) {
			$className = self::get_controller_class();
			$optionalController = new $className();
		}
		if(!$validator) {
			$className = self::get_validator_class();
			$validator = new $className();
		}
		parent::__construct($optionalController, $name, $fields, $actions, $validator);
	}

	function redirect($status = "success", $message = ""){
		return ShoppingCart::return_message($status, $message);
	}

}


/**
 *NOTE: this extends the standard OrderModifierFor to include AjaxSubmit functionality
 * So that customers can submit the form using AJAX
 *
 **/

class OrderModifierForm_AjaxSubmit extends OrderModifierForm {

	/**
	 *
	 *@param $optionalController Controller
	 *@param $name String
	 *@param $fields FieldSet
	 *@param $actions FieldSet
	 *@param $validator SS_Validator
	 **/

	function __construct($optionalController = null, $name, FieldSet $fields, FieldSet $actions,$validator = null){
		//jQuery.Form is included to make Ajax-based submission available.
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
		parent::__construct($optionalController, $name, $fields, $actions, $validator);
	}

}

/**
 * This controller allows you to submit modifier forms from anywhere on the site, especially the cart page.
 */
class OrderModifierForm_Controller extends Controller{

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
		//See issue 149
	}

}


class OrderModifierForm_Validator extends RequiredFields{


}

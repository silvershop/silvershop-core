<?php
/**
 * Stepped checkout provides multiple forms and actions for placing an order
 * 
 * @package shop
 * @subpackage steppedcheckout
 */
class SteppedCheckout extends Extension{

	private static $first_step = null; //action to show on index
	
	private static $steps = null;
	
	/**
	 * Set up CheckoutPage_Controller decorators for managing steps 
	 */
	static function setupSteps($steps = null){
		if(!$steps){
			$steps = array(
				'membership' => 'CheckoutStep_Membership',
				'contactdetails' => 'CheckoutStep_ContactDetails',
				'shippingaddress' => 'CheckoutStep_Address',
				'billingaddress' => 'CheckoutStep_Address',
				//'shippingmethod' => 'CheckoutStep_ShippingMethod', //currently in the shippingframework submodule
				'paymentmethod' => 'CheckoutStep_PaymentMethod',
				'summary' => 'CheckoutStep_Summary'
			);
		}
		Object::add_extension("CheckoutPage_Controller", "SteppedCheckout");
		foreach($steps as $action => $classname){
			Object::add_extension("CheckoutPage_Controller", $classname);
		}
		if(!self::config()->first_step){
			reset($steps);
			self::config()->first_step = key($steps);
		}
		self::$steps = $steps;
	}
	
	/**
	 * Redirect back to start of checkout if no cart started
	 */
	function onAfterInit(){
		$action = $this->owner->getRequest()->param('Action');
		if(!ShoppingCart::curr() && !empty($action) && isset(self::$steps[$action])){
			Controller::curr()->redirect($this->owner->Link());
			return;
		}
	}
	
	/**
	 * Check if passed action is the same as the current step
	 */
	function IsCurrentStep($name){
		if($this->owner->getAction() === $name){
			return true;
		}
		if($this->owner->getAction() == "index" && $this->actionPos($name) === 0){
			return true;
		}
		return false;
	}
	
	/**
	 * Check if passed action is for a step before current
	 */
	function IsPastStep($name){
		//echo "ispast ";
		return $this->compareActions($name,$this->owner->getAction()) < 0;
	}
	
	/**
	 * Check if passed action is for a step after current
	 */
	function IsFutureStep($name){
		//echo "isfuture ";
		return $this->compareActions($name,$this->owner->getAction()) > 0;
	}
	
	/**
	 * Get first step from stored steps
	 */
	function index(){
		if(self::config()->first_step){
			return $this->owner->{self::config()->first_step}();
		}
		return array();
	}
	
	/**
	 * Check if one step comes before or after the another
	 */
	private function compareActions($action1, $action2){
		return $this->actionPos($action1) - $this->actionPos($action2);
	}
	
	/**
	 * Get the numerical position of a step
	 */
	private function actionPos($incoming){
		$count = 0;
		foreach(self::$steps as $action => $step){
			if($action == $incoming){
				return $count;
			}
			$count++;
		}
	}
	
}
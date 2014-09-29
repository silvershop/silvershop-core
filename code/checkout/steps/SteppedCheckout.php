<?php
/**
 * Stepped checkout provides multiple forms and actions for placing an order
 *
 * @package shop
 * @subpackage steppedcheckout
 */
class SteppedCheckout extends Extension{

	/**
	 * Set up CheckoutPage_Controller decorators for managing steps
	 */
	public static function setupSteps($steps = null) {
		if(!is_array($steps)){
			//default steps
			$steps = array(
				'membership' => 'CheckoutStep_Membership',
				'contactdetails' => 'CheckoutStep_ContactDetails',
				'shippingaddress' => 'CheckoutStep_Address',
				'billingaddress' => 'CheckoutStep_Address',
				'paymentmethod' => 'CheckoutStep_PaymentMethod',
				'summary' => 'CheckoutStep_Summary'
			);
		}

		CheckoutPage::config()->steps = $steps;

		if(!CheckoutPage::config()->first_step){
			reset($steps);
			CheckoutPage::config()->first_step = key($steps);
		}
		//initiate extensions
		Object::add_extension("CheckoutPage_Controller", "SteppedCheckout");
		foreach($steps as $action => $classname){
			Object::add_extension("CheckoutPage_Controller", $classname);
		}
	}

	public function getSteps() {
		return CheckoutPage::config()->steps;
	}

	/**
	 * Redirect back to start of checkout if no cart started
	 */
	public function onAfterInit() {
		$action = $this->owner->getRequest()->param('Action');
		$steps = $this->getSteps();
		if(!ShoppingCart::curr() && !empty($action) && isset($steps[$action])){
			Controller::curr()->redirect($this->owner->Link());
			return;
		}
	}

	/**
	 * Check if passed action is the same as the current step
	 */
	public function IsCurrentStep($name) {
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
	public function IsPastStep($name) {
		return $this->compareActions($name, $this->owner->getAction()) < 0;
	}

	/**
	 * Check if passed action is for a step after current
	 */
	public function IsFutureStep($name) {
		return $this->compareActions($name, $this->owner->getAction()) > 0;
	}

	/**
	 * Get first step from stored steps
	 */
	public function index() {
		if(CheckoutPage::config()->first_step){
			return $this->owner->{CheckoutPage::config()->first_step}();
		}
		return array();
	}

	/**
	 * Check if one step comes before or after the another
	 */
	private function compareActions($action1, $action2) {
		return $this->actionPos($action1) - $this->actionPos($action2);
	}

	/**
	 * Get the numerical position of a step
	 */
	private function actionPos($incoming) {
		$count = 0;
		foreach($this->getSteps() as $action => $step){
			if($action == $incoming){
				return $count;
			}
			$count++;
		}
	}

}

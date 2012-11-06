<?php

class SteppedCheckout extends Extension{

	static $first_step = null; //action to show on index
	
	protected static $steps = null;
	
	/**
	 * Set up CheckoutPage_Controller decorators for managing steps 
	 */
	static function setupSteps($steps = null){
		if(!$steps){
			$steps = array(
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
		if(!self::$first_step){
			reset($steps);
			self::$first_step = key($steps);
		}
		self::$steps = $steps;
	}
	
	private function compareActions($action1, $action2){
		$comp =  $this->actionPos($action1) - $this->actionPos($action2);
		//echo "( $action1 $action2 $comp ) <br/>";
		return $comp;
	}
	
	private function actionPos($incoming){
		$count = 0;
		foreach(self::$steps as $action => $step){
			if($action == $incoming){
				return $count;
			}
			$count++;
		}
	}
	
	/**
	 * checks if passed action is the same as the current step
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
	 * checks if passed action is for a step before current
	 */
	function IsPastStep($name){
		//echo "ispast ";
		return $this->compareActions($name,$this->owner->getAction()) < 0;
	}
	
	/**
	 * checks if passed action is for a step after current
	 */
	function IsFutureStep($name){
		//echo "isfuture ";
		return $this->compareActions($name,$this->owner->getAction()) > 0;
	}
	
	/**
	 * get first step from stored steps
	 */
	function index(){
		if(self::$first_step){
			return $this->owner->{self::$first_step}();
		}
		return array();
	}
	
}
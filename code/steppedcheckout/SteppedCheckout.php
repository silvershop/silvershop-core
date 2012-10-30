<?php

//TODO: validation - provide validation for each step, and re-validate each request
//if validation fails at any step, then jump back to that step, with a message

class SteppedCheckout extends Extension{

	protected static $steps = null;
	
	static function setupSteps($steps){
		foreach($steps as $action => $classname){
			Object::add_extension("CheckoutPage_Controller", $classname);
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
		return $this->owner->contactdetails();
	}
	
}
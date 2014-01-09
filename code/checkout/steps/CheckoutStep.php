<?php
/**
 * Base class for building steps for checkout processing
 */
class CheckoutStep extends Extension{
	
	static $continue_anchor = "cont"; //indicates where to jump to on the page
	
	/**
	 * Get the next step action
	 * @return string|NULL
	 */
	private function nextstep(){
		$steps = $this->owner->getSteps();
		$found = false;
		foreach($steps as $step => $class){
			if(method_exists($this, $step)){ //determine if current step
				$found = true;
			}elseif ($found){
				return $step;
			}
		}
		return null;
	}
	
	function NextStepLink($nextstep = null){
		if(!$nextstep){
			$nextstep = $this->nextstep();
		}
		return $this->owner->Link($nextstep)."#".self::$continue_anchor;
	}
	
}
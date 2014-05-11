<?php
/**
 * Base class for building steps for checkout processing
 */
class CheckoutStep extends Extension{

	 //indicates where to jump to on the page
	public static $continue_anchor;

	/**
	 * Get the next step action
	 * @return string|NULL
	 */
	private function nextstep() {
		$steps = $this->owner->getSteps();
		$found = false;
		foreach($steps as $step => $class){
			//determine if this is the current step
			if(method_exists($this, $step)){
				$found = true;
			}elseif ($found){
				return $step;
			}
		}
		return null;
	}

	public function NextStepLink($nextstep = null) {
		if(!$nextstep){
			$nextstep = $this->nextstep();
		}
		$anchor = self::$continue_anchor ? "#".self::$continue_anchor: "";
		return $this->owner->Link($nextstep).$anchor;
	}

}

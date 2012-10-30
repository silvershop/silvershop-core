<?php
/**
 * Base class for building steps for checkout processing
 */
class CheckoutStep extends Extension{
	
	static $continue_anchor = "cont"; //indicates where to jump to on the page
	
	function NextStepLink($nextstep = null){
		return $this->owner->Link($nextstep)."#".self::$continue_anchor;
	}
	
}
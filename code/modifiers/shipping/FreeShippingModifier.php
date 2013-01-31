<?php

class FreeShippingModifier extends ShippingModifier{
	
	/**
	 * Calculate whether the current order is eligable for free shipping
	 */
	function eligable(){
		
	}
	
	function TableValue(){
		return _t("FreeShippingModifier.FREE","FREE");
	}
	
}
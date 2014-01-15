<?php

class ShippingModifier extends OrderModifier{
	
	private static $singular_name = "Shipping";
	
	function required(){
		return true; //TODO: make it optional
	}
	
	function requiredBeforePlace(){
		return true;
	}
	
}
<?php
/**
 * SubTotal modifier provides a way to display subtotal within the list of modifiers.
 * @package ecommerce
 */
class SubTotalModifier extends OrderModifier {
	
	protected static $is_chargable = false;
	
	function TableValue() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal($this->class,true);	
	}
	
	function CanRemove(){
		return false;
	}
	
	function TableTitle(){
		return 'Sub Total'; //TODO: lang
	}
	
}

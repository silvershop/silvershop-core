<?php
/**
 * SubTotal modifier provides a way to display subtotal within the list of modifiers.
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class SubTotalModifier extends OrderModifier {

	protected static $is_chargable = false;

	/**
	 * This overrides the table value to show the subtotal, but the LiveAmount is always 0 (see below)
	 */
	function TableValue() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal($this->class,true);
	}

	function LiveAmount(){
		return 0;
	}

	function CanRemove(){
		return false;
	}

	function TableTitle(){
		return _t("SubtTotalModifier.SUBTOTAL", "Sub Total");
	}

}

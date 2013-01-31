<?php

/**
 * Provide functions on order to help with ajax updates.
 * These functions were extracted out of the Order class.
 */
class OrderAJAX extends DataExtension{
	
	// Order Template and ajax Management
	
	function updateForAjax(array &$js) {
		$subTotal = DBField::create('Currency', $this->SubTotal())->Nice();
		$total = DBField::create('Currency', $this->Total())->Nice() . ' ' . Payment::site_currency();
		$js[] = array('id' => $this->TableSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->TableTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->OrderForm_OrderForm_AmountID(), 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => $this->CartSubTotalID(), 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => $this->CartTotalID(), 'parameter' => 'innerHTML', 'value' => $total);
	}
	
	function TableSubTotalID() {
		return 'Table_Order_SubTotal';
	}
	
	function TableTotalID() {
		return 'Table_Order_Total';
	}
	
	function OrderForm_OrderForm_AmountID() {
		return 'OrderForm_OrderForm_Amount';
	}
	
	function CartSubTotalID() {
		return 'Cart_Order_SubTotal';
	}
	
	function CartTotalID() {
		return 'Cart_Order_Total';
	}
	
}
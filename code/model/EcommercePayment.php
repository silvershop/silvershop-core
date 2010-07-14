<?php
/**
 * Customisations to {@link Payment} specifically
 * for the ecommerce module.
 * 
 * @package ecommerce
 */
class EcommercePayment extends DataObjectDecorator {
	
	function extraStatics() {
		
		//Customise model admin summary fields
		
		//These will break dataobject summary, searchable, default fields - should be on Payment
		/*
		Payment::$summary_fields['ID'] = 'ID';
		Payment::$summary_fields['Created'] = 'Created';
		Payment::$summary_fields['ClassName'] = 'Type';
		Payment::$summary_fields['PaidBy.Name'] = 'Member';
		Payment::$summary_fields['OrderID'] = 'Order ID';
		Payment::$summary_fields['Status'] = 'Status';
		*/
		/*
		Payment::$searchable_fields = array(
			'ID','OrderID','ClassName','Status'
		);
		
		Payment::$default_sort = "Created DESC";
		*/
		
		return array(
			'has_one' => array(
				'Order' => 'Order'
			)
		);
	}
	
	function onBeforeWrite() {
		if($this->owner->Status == 'Success' && $this->owner->Order()) {
			$order = $this->owner->Order();
			$order->Status = 'Paid';
			$order->write();
			$order->sendReceipt();
		}
	}
	
	function redirectToOrder() {
		$order = $this->owner->Order();
		Director::redirect($order->Link());
		return;
	}
	
	function setPaidObject(DataObject $do){
		$this->owner->PaidForID = $do->ID;
		$this->owner->PaidForClass = $do->ClassName;		
	}

	
}
?>
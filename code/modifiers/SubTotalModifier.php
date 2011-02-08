<?php
/**
 * SubTotal modifier provides a way to display subtotal within the list of modifiers.
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class SubTotalModifier extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	public static $defaults = array(
		"Type" => "NoChange"
	);

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)
// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)

// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

// ######################################## *** form functions (e. g. showform and getform)
// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES

	/**
	 * This overrides the table value to show the subtotal, but the LiveAmount is always 0 (see below)
	 */
	public function TableValue() {
		$order = $this->Order();
		return $order->SubTotal() + $order->ModifiersSubTotal($this->class,true);
	}

	public function CanRemove(){
		return false;
	}

	public function TableTitle(){
		return _t("SubtTotalModifier.SUBTOTAL", "Sub Total");
	}



// ######################################## ***  inner calculations ... USED CALCULATED VALUES
// ######################################## *** calculate database fields: protected function Live[field name] ... USED CALCULATED VALUES


	protected function LiveAmount(){
		return 0;
	}

// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

	protected function IsNoChange() {
		return true;
	}


// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}

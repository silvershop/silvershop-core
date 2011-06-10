<?php
/**
 * Password input field.
 * @author nicolaas[at]sunnysideup.co.nz
 * @package forms
 */

class OptionalConfirmedPasswordField extends ConfirmedPasswordField {

	function __construct($name, $title = null, $value = "", $form = null, $showOnClick = false, $titleConfirmField = null) {
		parent::__construct($name, $title, $value, $form, $showOnClick, $titleConfirmField);
	}

	function Field() {
		Requirements::javascript(ECOMMERCE_DIR.'/javascript/OptionalConfirmedPasswordField.js');
		Requirements::block(SAPPHIRE_DIR . '/javascript/ConfirmedPasswordField.js');
		return parent::Field();
	}

	function setValue($value) {
		if(is_array($value)) {
			if($value['_Password'] || (!$value['_Password'] && !$this->canBeEmpty)) {
				$this->value = $value['_Password'];
			}
			if(isset($value['_PasswordFieldVisible'])){
				$this->children->fieldByName($this->Name() . '[_PasswordFieldVisible]')->setValue($value['_PasswordFieldVisible']);
			}
		} else {
			if($value || (!$value && !$this->canBeEmpty)) {
				$this->value = $value;
			}
		}
	}

}

<?php

class CheckedCheckboxField extends CheckboxField{
	
	protected $reqmessage = "You must check the box.";
	
	function setRequiredMessage($message){
		$this->reqmessage = $message;
	}
	
	function validate($validator){
		$value = trim($this->value);
		if(empty($value)){
 			$validator->validationError(
 				$this->name,
				$this->reqmessage,
				"validation"
			);
			return false;
		}
		return true;
	}
	
}
<?php
/**
 * ExpiryDate field, contains validation and formspec for expirydate fields.
 * @package forms
 * @subpackage fields-formattedinput
 */
class ExpiryDateField extends TextField {

	protected static $short_months = 1;
		static function set_short_months($boolean) {self::$short_months = $boolean;}
		static function get_short_months() {return self::$short_months;}

	function Field() {
		$monthValue = '';
		$yearValue = '';
		if(strlen($this->value) == 4) {
			$monthValue = substr($this->value, 0, 2);
			$yearValue = "20".substr($this->value, 2, 2);
		}
		$field = "
			<span id=\"{$this->name}_Holder\" class=\"expiryDateField\">
				<select class=\"expiryDate expiryDateMonth\" name=\"{$this->name}[0]\" >
					<option value=\"\" selected=\"selected\">Month</option>".$this->makeSelectList($this->monthArray(), $monthValue)."
				</select>
				<select class=\"expiryDate expiryDateYear\" name=\"{$this->name}[1]\" >
					<option value=\"\" selected=\"selected\">Year</option>".$this->makeSelectList($this->yearArray(), $yearValue)."
				</select>
			</span>";
		return $field;
	}
	function dataValue() {
		if(is_array($this->value)) {
			$string = '';
			foreach($this->value as $part) {
				$part = str_pad($part, 2, "0", STR_PAD_LEFT);
				$string .= trim($part);
			}
			return $string;
		}
		else {
			return $this->value;
		}
	}

	function jsValidation() {
		$formID = $this->form->FormName();
		$jsFunc =<<<JS
Behaviour.register({
	"#$formID": {
		validateExpiryDate: function(fieldName) {
			if(!$(fieldName + "_Holder")) return true;

			// Expiry Dates are split into multiple values, so get the inputs from the form.
			var fields = $(fieldName + "_Holder").getElementsByTagName('input');
			var error = false;
			if(fields[0].value == null || fields[0].value == "" || fields[1].value == null || fields[1].value == "") {
				error = true;
			}
			if(error){
				validationError(monthField,"Make sure to enter a valid expiration date.","validation",false);
				return false;
			}
			return true;
		}
	}
});
JS;
		Requirements::customScript($jsFunc, 'func_validateExpiryDate');
		return "\$('$formID').validateExpiryDate('$this->name');";
	}

	function validate($validator){
		// If the field is empty then don't return an invalidation message'
		if(!isset($this->value[0])) {
			$validator->validationError(
				$this->name,
				"Please ensure you have entered the expiry date month correctly.",
				"validation",
				false
			);
			return false;
		}
		if(!isset($this->value[1])) {
			$validator->validationError(
				$this->name,
				"Please ensure you have entered the expiry date year correctly.",
				"validation",
				false
			);
			return false;
		}
		$value = str_pad($this->value[0], 2, "0", STR_PAD_LEFT);
		$value .= str_pad($this->value[1], 2, "0", STR_PAD_LEFT);
		$this->value = $value;
		// months are entered as a simple number (e.g. 1,2,3, we add a leading zero if needed)
		$monthValue = substr($this->value, 0, 2);
		$yearValue = "20".substr($this->value, 2, 2);
		$ts = strtotime(Date("Y-m-01"))-(60*60*24);
		$expiryTs = strtotime("20".$yearValue."-".$monthValue."-01");
		if($ts > $expiryTs) {
			$validator->validationError(
				$this->name,
				"Please ensure you have entered the expiry date correctly.",
				"validation",
				false
			);
			return false;
		}
		return true;
	}

	protected function yearArray() {
		$list = array();
		$i = 0;
		for($i = 0; $i < 12; $i++) {
			$ts = strtotime("+".$i." year");
			$list[Date("y", $ts)] = Date("Y", $ts);
		}
		return $list;
	}


	protected function makeSelectList($array, $currentValue) {
		$string = '';
		foreach($array as $key => $value) {
			$select = '';
			if($key == $currentValue) {
				$select = ' selected="selected"';
			}
			$string .= '<option value="'.$key.'"'.$select.'>'.$value.'</option>';
		}
		return $string;
	}

	protected function monthArray() {
		if(self::$short_months) {
		  return array(
				1 => "01 | Jan",
				2 => "02 | Feb",
				3 => "03 | Mar",
				4 => "04 | Apr",
				5 => "05 | May",
				6 => "06 | Jun",
				7 => "07 | Jul",
				8 => "08 | Aug",
				9 => "09 | Sep",
				10 => "10 | Oct",
				11 => "11 | Nov",
				12 => "12 | Dec"
			);
		}
		else {
		  return array(
				1 => "January",
				2 => "February",
				3 => "March",
				4 => "April",
				5 => "May",
				6 => "June",
				7 => "July",
				8 => "August",
				9 => "September",
				10 => "October",
				11 => "November",
				12 => "December"
			);
		}
	}

}

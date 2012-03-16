<?php
/**
 * Improvements to Currency for presenting in templates.
 */
class EcommerceCurrency extends Currency {

	protected static $decimalDelimiter = '.';
	protected static $thousandDelimiter = ',';
	protected static $negative_value_format = "<span class=\"negative\">(%s)</span>";

	public static function getCurrencySymbol() {
		return self::$currencySymbol;
	}

	function Nice() {
		$val = self::$currencySymbol . number_format(abs($this->value), 2, self::$decimalDelimiter, self::$thousandDelimiter);
		if($this->value < 0){
			return sprintf(self::$negative_value_format,$val);
		}
		return $val;
	}

	static function setDecimalDelimiter($value) {
		self::$decimalDelimiter = $value;
	}
	static function setThousandDelimiter($value) {
		self::$thousandDelimiter = $value;
	}
	
	static function getDecimalDelimiter() {
		return self::$decimalDelimiter;
	}
	static function getThousandDelimiter() {
		return self::$thousandDelimiter;
	}
}
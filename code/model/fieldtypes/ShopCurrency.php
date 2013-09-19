<?php
/**
 * Improvements to Currency for presenting in templates.
 */
class ShopCurrency extends Currency {

	protected static $decimal_delimiter = '.';
	protected static $thousand_delimiter = ',';
	protected static $negative_value_format = "<span class=\"negative\">(%s)</span>";

	public static function getCurrencySymbol() {
		return Currency::config()->currency_symbol;
	}

	function Nice() {
		$val = self::getCurrencySymbol() . number_format(abs($this->value), 2, self::getDecimalDelimiter(), self::getThousandDelimiter());
		if($this->value < 0){
			return sprintf(self::$negative_value_format,$val);
		}
		return $val;
	}

	static function setDecimalDelimiter($value) {
		self::$decimal_delimiter = $value;
	}
	static function setThousandDelimiter($value) {
		self::$thousand_delimiter = $value;
	}
	
	static function getDecimalDelimiter() {
		return self::$decimal_delimiter;
	}
	static function getThousandDelimiter() {
		return self::$thousand_delimiter;
	}
}
<?php
class EcommerceCurrency extends Currency {

	protected static $decimal_delimiter = '.';
		static function set_decimal_delimiter($value) {self::$decimal_delimiter = $value;}
		static function get_decimal_delimiter() {return self::$decimal_delimiter;}

	protected static $thousand_delimiter = '';
		static function set_thousand_delimiter($value) {self::$thousand_delimiter = $value;}
		static function get_thousand_delimiter() {return self::$thousand_delimiter;}

	public static function get_currency_symbol() {
		return self::$currencySymbol;
	}

	function Nice() {
		$val = self::$currencySymbol . number_format(abs($this->value), 2, self::$decimal_delimiter, self::$thousand_delimiter);
		if($this->value < 0) return "($val)";
		else return $val;
	}

}

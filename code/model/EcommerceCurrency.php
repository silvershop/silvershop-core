<?php
/**
 * @deprecated use instead: http://code.google.com/p/silverstripe-i18n-fieldtypes/
 * @author ivo.bathke
 */
class EcommerceCurrency extends Currency {

	protected static $decimal_delimiter = '.';
		static function set_decimal_delimiter(string $s) {self::$decimal_delimiter = $s;}
		static function get_decimal_delimiter() {return self::$decimal_delimiter;}

	protected static $thousand_delimiter = '';
		static function set_thousand_delimiter(string $s) {self::$thousand_delimiter = $s;}
		static function get_thousand_delimiter() {return self::$thousand_delimiter;}

	public static function get_currency_symbol() {
		return self::$currencySymbol;
	}

	/**
	 *@return string (e.g. $1,000.02 or ($99.76) )
	 *
	 **/

	function Nice() {
		$val = self::$currencySymbol . number_format(abs($this->value), 2, self::$decimal_delimiter, self::$thousand_delimiter);
		if($this->value < 0) {
			return "($val)";
		}
		else {
			return $val;
		}
	}

}

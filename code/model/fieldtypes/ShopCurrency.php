<?php

/**
 * Improvements to Currency for presenting in templates.
 *
 * @package shop
 */
class ShopCurrency extends Currency {

	private static $decimal_delimiter = '.';

	private static $thousand_delimiter = ',';
	
	private static $negative_value_format = "<span class=\"negative\">(%s)</span>";

	public function Nice() {
		$val = $this->config()->currency_symbol .
			number_format(
				abs($this->value), 2,
				self::config()->decimal_delimiter,
				self::config()->thousand_delimiter
			);
		if($this->value < 0){
			return sprintf(self::config()->negative_value_format,$val);
		}

		return $val;
	}

	public function forTemplate() {
		return $this->Nice();
	}

	/**
	 * If no cents on the price, trim those off.
	 *
	 * @return string
	 */
	public function TrimCents() {
		$val = $this->value;

		if(floor($val) == $val) {
			return floor($val);
		}

		return $val;
	}
}

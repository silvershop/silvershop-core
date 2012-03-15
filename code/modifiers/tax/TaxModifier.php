<?php

/**
 * Base class for creating tax modifiers with.
 */
class TaxModifier extends OrderModifier{
	
	public static $db = array(
		'Rate' => 'Double'
	);
	
	public static $defaults = array(
		'Rate' => 0.15 //15% tax
	);
	
	public static $singular_name = "Tax";
	function i18n_singular_name() {
		return _t("TaxModifier.SINGULAR", self::$singular_name);
	}
	public static $plural_name = "Taxes";
	function i18n_plural_name() {
		return _t("TaxModifier.PLURAL", self::$plural_name);
	}
	
	function TableTitle(){
		$title =  parent::TableTitle();
		if($this->Rate)
			$title .= " ".sprintf(_t("TaxModifier.ATRATE","@ %s"),number_format($this->Rate * 100, 1)."%");
		return $title;
	}
	
}
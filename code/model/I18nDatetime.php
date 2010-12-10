<?php
/**
 * provides I18n formating
 * 
 * @package ecommerce
 */
class I18nDatetime extends SS_Datetime{

	/**
	 * Returns the datetime in the format given in the lang file
	 * locale sould be set
	 */
	function Nice() {
		if($this->value) return $this->FormatI18N(_t('General.DATETIMEFORMATNICE','%m/%d/%G %I:%M%p'));
	}
	function NiceDate() {
		if($this->value) return $this->FormatI18N(_t('General.DATEFORMATNICE','%m/%d/%G'));
	}
	function Nice24() {
		return date(_t('General.DATETIMEFORMATNICE24','d/m/Y H:i'), strtotime($this->value));
	}
}
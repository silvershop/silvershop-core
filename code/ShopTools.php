<?php

/**
 * Globally useful tools
 */
class ShopTools{

	public static function price_for_display($price) {
		$currency = ShopConfig::get_site_currency();
		$field = new Money("Price");
		$field->setAmount($price);
		$field->setCurrency($currency);
		return $field;
	}

    public static function get_current_locale()
    {
        if(class_exists('Translatable')){
            return Translatable::get_current_locale();
        }

        if(class_exists('Fluent')){
            return Fluent::current_locale();
        }

        return i18n::get_locale();
    }

    public static function install_locale($locale)
    {
        // If the locale isn't given, silently fail (there might be carts that still have locale set to null)
        if(empty($locale)){
            return;
        }

        if(class_exists('Translatable')){
            Translatable::set_current_locale($locale);
        } else if(class_exists('Fluent')){
            Fluent::set_persist_locale($locale);
        }

        // Do something like Fluent does to install the locale
        i18n::set_locale($locale);

        // LC_NUMERIC causes SQL errors for some locales (comma as decimal indicator) so skip
        foreach (array(LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_TIME) as $category) {
            setlocale($category, "{$locale}.UTF-8", $locale);
        }
        // Get date/time formats from Zend
        require_once 'Zend/Date.php';
        i18n::config()->date_format = Zend_Locale_Format::getDateFormat($locale);
        i18n::config()->time_format = Zend_Locale_Format::getTimeFormat($locale);
    }

}

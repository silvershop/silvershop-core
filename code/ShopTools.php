<?php

/**
 * Globally useful tools
 */
class ShopTools
{
    /**
     * Get the DB connection in a SS 3.1 and 3.2+ compatible way
     * @param string $name
     * @return SS_Database
     */
    public static function DBConn($name = 'default')
    {
        if (method_exists('DB', 'get_conn')) {
            return DB::get_conn($name);
        }
        return DB::getConn($name);
    }

    /**
     * Convert a numeric price to the shop currency
     * @param mixed $price the price to convert
     * @return Money the price wrapped in a Money DBField to be used for templates or similar
     */
    public static function price_for_display($price)
    {
        $currency = ShopConfig::get_site_currency();
        $field = Money::create("Price");
        $field->setAmount($price);
        $field->setCurrency($currency);
        return $field;
    }

    /**
     * Get the current locale.
     * Tries to get the locale from Translatable, Fluent or the default i18n (depending on what is installed)
     * @return string the locale in use
     */
    public static function get_current_locale()
    {
        if (class_exists('Translatable')) {
            return Translatable::get_current_locale();
        }

        if (class_exists('Fluent')) {
            return Fluent::current_locale();
        }

        return i18n::get_locale();
    }

    /**
     * Set/Install the given locale.
     * This does set the i18n locale as well as the Translatable or Fluent locale (if any of these modules is installed)
     * @param string $locale the locale to install
     * @throws Zend_Locale_Exception @see Zend_Locale_Format::getDateFormat and @see Zend_Locale_Format::getTimeFormat
     */
    public static function install_locale($locale)
    {
        // If the locale isn't given, silently fail (there might be carts that still have locale set to null)
        if (empty($locale)) {
            return;
        }

        if (class_exists('Translatable')) {
            Translatable::set_current_locale($locale);
        } else {
            if (class_exists('Fluent')) {
                Fluent::set_persist_locale($locale);
            }
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

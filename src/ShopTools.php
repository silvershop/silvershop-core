<?php

namespace SilverShop;

use Psr\Container\NotFoundExceptionInterface;
use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBMoney;

/**
 * Globally useful tools
 */
class ShopTools
{

    /**
     * Convert a numeric price to the shop currency
     *
     * @param  mixed $price the price to convert
     * @return DBMoney the price wrapped in a Money DBField to be used for templates or similar
     */
    public static function price_for_display($price): DBMoney
    {
        $currency = ShopConfigExtension::get_site_currency();
        $dbMoney = DBMoney::create_field(DBMoney::class, 0, 'Price');
        $dbMoney->setAmount($price);
        $dbMoney->setCurrency($currency);
        return $dbMoney;
    }

    /**
     * Get the current locale.
     * Tries to get the locale from Fluent or the default i18n (depending on what is installed)
     *
     * @return string the locale in use
     */
    public static function get_current_locale(): string
    {
        if (class_exists('TractorCow\Fluent\State\FluentState')) {
            return singleton('TractorCow\Fluent\State\FluentState')->getLocale();
        }

        return i18n::get_locale();
    }

    /**
     * Set/Install the given locale.
     * This does set the i18n locale as well as the Fluent locale (if this module is installed)
     *
     * @param string $locale the locale to install
     */
    public static function install_locale($locale): void
    {
        // If the locale isn't given, silently fail (there might be carts that still have locale set to null)
        if (empty($locale)) {
            return;
        }

        if (class_exists('TractorCow\Fluent\State\FluentState')) {
            singleton('TractorCow\Fluent\State\FluentState')->setLocale($locale);
        }

        // Do something like Fluent does to install the locale
        i18n::set_locale($locale);

        // LC_NUMERIC causes SQL errors for some locales (comma as decimal indicator) so skip
        foreach ([LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_TIME] as $category) {
            setlocale($category, "{$locale}.UTF-8", $locale);
        }
    }

    /**
     * Get the current section (first looking at controller, then at a request instance and lastly return a fresh session)
     *
     * @param HTTPRequest $httpRequest the incoming request (optional)
     */
    public static function getSession(?HTTPRequest $httpRequest = null): Session
    {
        if ($httpRequest && ($session = $httpRequest->getSession())) {
            return $session;
        }

        if (Controller::has_curr() && ($httpRequest = Controller::curr()->getRequest())) {
            return $httpRequest->getSession();
        }

        try {
            if ($session = Injector::inst()->get(HTTPRequest::class)->getSession()) {
                return $session;
            }
        } catch (NotFoundExceptionInterface $e) {
            // No-Op
        }

        return new Session([]);
    }

    /**
     * Sanitise a model class' name for inclusion in a link
     *
     * @param  string $class
     */
    public static function sanitiseClassName($class): string
    {
        return str_replace('\\', '-', $class);
    }

    /**
     * Unsanitise a model class' name from a URL param
     *
     * @param  string $class
     */
    public static function unsanitiseClassName($class): string
    {
        return str_replace('-', '\\', $class);
    }
}

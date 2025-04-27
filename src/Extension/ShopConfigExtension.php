<?php

namespace SilverShop\Extension;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Security\Group;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * @property ?string $AllowedCountries
 * @property int $TermsPageID
 * @property int $CustomerGroupID
 * @property int $DefaultProductImageID
 * @method SiteTree TermsPage()
 * @method Group CustomerGroup()
 * @method Image DefaultProductImage()
 * @extends Extension<(SiteConfig & static)>
 */
class ShopConfigExtension extends Extension
{
    use Configurable;

    private static array $db = [
        'AllowedCountries' => 'Text',
    ];

    private static array $has_one = [
        'TermsPage' => SiteTree::class,
        'CustomerGroup' => Group::class,
        'DefaultProductImage' => Image::class,
    ];

    private static array $owns = [
        'DefaultProductImage'
    ];

    /**
     * Email address where shop emails should be sent from
     */
    private static $email_from;

    /**
     * The shop base currency
     */
    private static string $base_currency = 'NZD';

    private static bool $forms_use_button_tag = false;

    public static function current(): SiteConfig
    {
        return SiteConfig::current_site_config();
    }

    public static function get_site_currency(): string
    {
        return self::config()->base_currency;
    }

    public function updateCMSFields(FieldList $fieldList): void
    {
        $fieldList->insertBefore('Access', $shoptab = Tab::create('Shop', 'Shop'));
        $fieldList->addFieldToTab(
            'Root.Shop',
            TabSet::create(
                'ShopTabs',
                $maintab = Tab::create(
                    'Main',
                    TreeDropdownField::create(
                        'TermsPageID',
                        _t(__CLASS__ . '.TermsPage', 'Terms and Conditions Page'),
                        SiteTree::class
                    ),
                    TreeDropdownField::create(
                        'CustomerGroupID',
                        _t(__CLASS__ . '.CustomerGroup', 'Group to add new customers to'),
                        Group::class
                    ),
                    UploadField::create('DefaultProductImage', _t(__CLASS__ . '.DefaultImage', 'Default Product Image'))
                ),
                $countriesTab = Tab::create(
                    'Countries',
                    CheckboxSetField::create(
                        'AllowedCountries',
                        _t(__CLASS__ . '.AllowedCountries', 'Allowed Ordering and Shipping Countries'),
                        self::config()->iso_3166_country_codes
                    )
                )
            )
        );
        $fieldList->removeByName('CreateTopLevelGroups');
        $countriesTab->setTitle(_t(__CLASS__ . '.AllowedCountriesTabTitle', 'Allowed Countries'));
    }

    /**
     * Get list of allowed countries
     *
     * @param boolean $prefixisocode - prefix the country code
     */
    public function getCountriesList($prefixisocode = false): array
    {
        $countries = self::config()->iso_3166_country_codes;
        asort($countries);
        if ($allowed = $this->owner->AllowedCountries) {
            $allowed = json_decode($allowed);
            if (!empty($allowed)) {
                $countries = array_intersect_key($countries, array_flip($allowed));
            }
        }
        if ($prefixisocode) {
            foreach ($countries as $key => $value) {
                $countries[$key] = "$key - $value";
            }
        }
        return $countries;
    }

    /**
     * For shops that only sell to a single country,
     * this will return the country code, otherwise null.
     */
    public function getSingleCountry(bool $fullname = false): ?string
    {
        $countries = $this->getCountriesList();
        if (count($countries) == 1) {
            if ($fullname) {
                return array_pop($countries);
            } else {
                reset($countries);
                return key($countries);
            }
        }
        return null;
    }

    /**
     * Convert iso country code to English country name
     */
    public static function countryCode2name($code): string
    {
        $codes = self::config()->iso_3166_country_codes;
        if (isset($codes[$code])) {
            return $codes[$code];
        }
        return $code;
    }
}

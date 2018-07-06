<?php

namespace SilverShop\Forms;

use SilverShop\ShopUserInfo;
use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\SiteConfig\SiteConfig;

class SetLocationForm extends Form
{
    public function __construct($controller, $name = "SetLocationForm")
    {
        $countries = SiteConfig::current_site_config()->getCountriesList();
        $fields = FieldList::create(
            $countryfield = DropdownField::create("Country", _t(__CLASS__ . '.Country', 'Country'), $countries)
        );
        $countryfield->setHasEmptyDefault(true);
        $countryfield->setEmptyString(_t(__CLASS__ . '.ChooseCountry', 'Choose country...'));
        $actions = FieldList::create(
            FormAction::create("setLocation", "set")
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );
        parent::__construct($controller, $name, $fields, $actions);
        //load currently set location
        if ($location = singleton(ShopUserInfo::class)->getLocation()) {
            $countryfield->setHasEmptyDefault(false);
            $this->loadDataFrom($location);
        }
    }

    public function setLocation($data, $form)
    {
        singleton(ShopUserInfo::class)->setLocation($data);
        $this->controller->redirectBack();
    }
}

<?php

namespace SilverShop\Forms;

use SilverStripe\Control\RequestHandler;
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
    public function __construct(RequestHandler $requestHandler, $name = "SetLocationForm")
    {
        $countries = SiteConfig::current_site_config()->getCountriesList();
        $fieldList = FieldList::create(
            $countryfield = DropdownField::create("Country", _t(__CLASS__ . '.Country', 'Country'), $countries)
        );
        $countryfield->setHasEmptyDefault(true);
        $countryfield->setEmptyString(_t(__CLASS__ . '.ChooseCountry', 'Choose country...'));
        $actions = FieldList::create(
            FormAction::create("setLocation", "set")
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );
        parent::__construct($requestHandler, $name, $fieldList, $actions);
        //load currently set location
        if ($location = singleton(ShopUserInfo::class)->getLocation()) {
            $countryfield->setHasEmptyDefault(false);
            $this->loadDataFrom($location);
        }
    }

    public function setLocation(array $data, Form $form): void
    {
        singleton(ShopUserInfo::class)->setLocation($data);
        $this->controller->redirectBack();
    }
}

<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\SiteConfig\SiteConfig;

class Terms extends CheckoutComponent
{
    public function getFormFields(Order $order): FieldList
    {
        $fieldList = FieldList::create();
        $page = SiteConfig::current_site_config()->TermsPage();

        if ($page->exists()) {
            $fieldList->push(
                CheckboxField::create(
                    'ReadTermsAndConditions',
                    DBField::create_field('HTMLText', _t(
                        'SilverShop\Checkout\Checkout.TermsAndConditionsLink',
                        'I agree to the terms and conditions stated on the <a href="{TermsPageLink}" target="new" title="Read the shop terms and conditions for this site">{TermsPageTitle}</a> page',
                        '',
                        ['TermsPageLink' => $page->Link(), 'TermsPageTitle' => $page->Title]
                    ))
                )->setCustomValidationMessage(
                    _t('SilverShop\Checkout\CheckoutField.MustAgreeToTerms', 'You must agree to the terms and conditions')
                )
            );
        }

        return $fieldList;
    }

    public function validateData(Order $order, array $data): bool
    {
        return true;
    }

    public function getData(Order $order): array
    {
        return [];
    }

    public function setData(Order $order, array $data): Order
    {
        return $order;
    }

    public function getRequiredFields(Order $order): array
    {
        $fields = parent::getRequiredFields($order);

        if (SiteConfig::current_site_config()->TermsPage()->exists()) {
            $fields[] = 'ReadTermsAndConditions';
        }

        return $fields;
    }
}

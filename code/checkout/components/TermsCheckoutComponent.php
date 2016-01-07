<?php

class TermsCheckoutComponent extends CheckoutComponent
{
    public function getFormFields(Order $order)
    {
        $fields = FieldList::create();
        $page = SiteConfig::current_site_config()->TermsPage();

        if ($page->exists()) {
            $fields->push(
                CheckboxField::create(
                    'ReadTermsAndConditions',
                    _t(
                        'Checkout.TermsAndConditionsLink',
                        'I agree to the terms and conditions stated on the <a href="{TermsPageLink}" target="new" title="Read the shop terms and conditions for this site">{TermsPageTitle}</a> page',
                        '',
                        array('TermsPageLink' => $page->Link(), 'TermsPageTitle' => $page->Title)
                    )
                )->setCustomValidationMessage(
                    _t("CheckoutField.MustAgreeToTerms", "You must agree to the terms and conditions")
                )
            );
        }

        return $fields;
    }

    public function validateData(Order $order, array $data)
    {
        return true;
    }

    public function getData(Order $order)
    {
        return array();
    }

    public function setData(Order $order, array $data)
    {
    }

    public function getRequiredFields(Order $order)
    {
        $fields = parent::getRequiredFields($order);

        if (SiteConfig::current_site_config()->TermsPage()->exists()) {
            $fields[] = 'ReadTermsAndConditions';
        }

        return $fields;
    }
}

<?php

namespace SilverShop\Checkout;

use SilverShop\Model\Address;
use SilverShop\Page\CheckoutPage;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Factory for generating checkout fields
 *
 * @todo
 */
class CheckoutFieldFactory
{
    private static ?\SilverShop\Checkout\CheckoutFieldFactory $checkoutFieldFactory = null;

    public static function singleton(): ?\SilverShop\Checkout\CheckoutFieldFactory
    {
        if (!self::$checkoutFieldFactory instanceof \SilverShop\Checkout\CheckoutFieldFactory) {
            self::$checkoutFieldFactory = new CheckoutFieldFactory();
        }
        return self::$checkoutFieldFactory;
    }

    //prevent instantiation
    private function __construct()
    {
    }

    public function getContactFields($subset = []): FieldList
    {
        return $this->getSubset(
            FieldList::create(
                TextField::create('FirstName', _t('SilverShop\Model\Order.db_FirstName', 'First Name')),
                TextField::create('Surname', _t('SilverShop\Model\Order.db_Surname', 'Surname')),
                EmailField::create('Email', _t('SilverShop\Model\Order.db_Email', 'Email'))
            ),
            $subset
        );
    }

    public function getAddressFields($type = "shipping", $subset = []): FieldList
    {
        $address = singleton(Address::class);
        $fields = $address->getFormFields($type);
        return $this->getSubset($fields, $subset);
    }

    public function getMembershipFields(): FieldList
    {
        $fieldList = $this->getContactFields();
        $idfield = Member::config()->unique_identifier_field;
        if (!$fieldList->fieldByName($idfield)) {
            $fieldList->push(TextField::create($idfield, $idfield)); //TODO: scaffold the correct id field
        }
        $fieldList->push($this->getPasswordField());
        return $fieldList;
    }

    public function getPasswordFields(): FieldList
    {
        $loginlink = "Security/login?BackURL=" . CheckoutPage::find_link(true);
        $fieldList = FieldList::create(
            HeaderField::create(_t('SilverShop\Checkout\CheckoutField.MembershipDetails', 'Membership Details'), 3),
            LiteralField::create(
                'MemberInfo',
                '<p class="message warning">' .
                _t(
                    'SilverShop\Checkout\CheckoutField.MemberLoginInfo',
                    'If you are already a member please <a href="{LoginUrl}">log in</a>',
                    '',
                    ['LoginUrl' => $loginlink]
                ) .
                '</p>'
            ),
            LiteralField::create(
                'AccountInfo',
                '<p>' . _t(
                    'SilverShop\Checkout\CheckoutField.AccountInfo',
                    'Please choose a password, so you can login and check your order history in the future'
                ) . '</p>'
            ),
            $pwf = $this->getPasswordField()
        );
        if (!Checkout::membership_required()) {
            $pwf->setCanBeEmpty(true);
        }
        return $fieldList;
    }

    public function getPaymentMethodFields(): OptionsetField
    {
        //TODO: only get one field if there is no option
        return OptionsetField::create(
            'PaymentMethod',
            _t('SilverShop\Checkout\CheckoutField.PaymentType', "Payment Type"),
            GatewayInfo::getSupportedGateways(),
            array_keys(GatewayInfo::getSupportedGateways())
        );
    }

    public function getPasswordField($confirmed = true): ConfirmedPasswordField|PasswordField
    {
        if ($confirmed) {
            return ConfirmedPasswordField::create('Password', _t('SilverShop\Checkout\CheckoutField.Password', 'Password'));
        }
        return PasswordField::create('Password', _t('SilverShop\Checkout\CheckoutField.Password', 'Password'));
    }

    public function getNotesField(): TextareaField
    {
        return TextareaField::create("Notes", _t("SilverShop\Model\Order.db_Notes", "Message"));
    }

    public function getTermsConditionsField(): CheckboxField
    {
        $field = null;

        if (SiteConfig::current_site_config()->TermsPage()->exists()) {
            $termsPage = SiteConfig::current_site_config()->TermsPage();

            $field = CheckboxField::create(
                'ReadTermsAndConditions',
                _t(
                    'SilverShop\Checkout\Checkout.TermsAndConditionsLink',
                    'I agree to the terms and conditions stated on the <a href="{TermsPageLink}" target="new" title="Read the shop terms and conditions for this site">{TermsPageTitle}</a> page',
                    '',
                    ['TermsPageLink' => $termsPage->Link(), 'TermsPageTitle' => $termsPage->Title]
                )
            );
        }

        return $field;
    }

    /**
     * Helper function for reducing a field set to a given subset,
     * in the given order.
     *
     * @param FieldList $fieldList form fields to take a subset from.
     * @param array     $subset list of field names to return as subset
     * @return FieldList subset of form fields
     */
    private function getSubset(FieldList $fieldList, $subset = []): FieldList
    {
        if (empty($subset)) {
            return $fieldList;
        }
        $subfieldlist = FieldList::create();
        foreach ($subset as $field) {
            if ($field = $fieldList->fieldByName($field)) {
                $subfieldlist->push($field);
            }
        }
        return $subfieldlist;
    }
}

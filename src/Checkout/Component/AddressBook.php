<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\i18n\i18nEntityProvider;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Adds the ability to use the member's address book for choosing addresses
 */
abstract class AddressBook extends Address implements i18nEntityProvider
{
    private static string $jquery_file = 'https://code.jquery.com/jquery-3.7.0.min.js';

    /**
     * The composite field tag to use
     */
    private static string $composite_field_tag = 'div';

    protected bool $addtoaddressbook = true;

    public function getFormFields(Order $order): FieldList
    {
        $fieldList = parent::getFormFields($order);

        if (($existingaddressfields = $this->getExistingAddressFields()) instanceof FieldList) {
            if ($jquery = $this->config()->get('jquery_file')) {
                Requirements::javascript($jquery);
                Requirements::javascript('silvershop/core:client/dist/javascript/CheckoutPage.js');
            } else {
                Requirements::javascript('silvershop/core:client/dist/javascript/CheckoutPage.nojquery.js');
            }

            // add the fields for a new address after the dropdown field
            $existingaddressfields->merge($fieldList);
            // group under a composite field (invisible by default) so we
            // easily know which fields to show/hide
            $label = _t(
                "SilverShop\Model\Address.{$this->addresstype}Address",
                "{$this->addresstype} Address"
            );

            return FieldList::create(
                CompositeField::create($existingaddressfields)
                    ->addExtraClass('hasExistingValues')
                    ->setLegend($label)
                    ->setTag(Config::inst()->get(self::class, 'composite_field_tag'))
            );
        }

        return $fieldList;
    }

    /**
     * Allow choosing from an existing address
     *
     * @return FieldList|null fields for
     */
    public function getExistingAddressFields(): ?FieldList
    {
        $member = Security::getCurrentUser();
        if ($member && $member->AddressBook()->exists()) {
            $addressoptions = $member->AddressBook()->sort('Created', 'DESC')->map('ID', 'toString')->toArray();
            $addressoptions['newaddress'] = _t('SilverShop\Model\Address.CreateNewAddress', 'Create new {AddressType} address', '', ["AddressType" => $this->addresstype]);
            $fieldtype = count($addressoptions) > 3 ? DropdownField::class : OptionsetField::class;

            $label = _t("SilverShop\Model\Address.Existing{$this->addresstype}Address", "Existing {$this->addresstype} Address");

            return FieldList::create(
                $fieldtype::create(
                    $this->addresstype . 'AddressID',
                    $label,
                    $addressoptions,
                    $member->{'Default' . $this->addresstype . 'AddressID'}
                )->addExtraClass('existingValues')
            );
        }

        return null;
    }

    /**
     * We don't know at the front end which fields are required so we defer to validateData
     *
     */
    public function getRequiredFields(Order $order): array
    {
        return [];
    }

    /**
     * @throws ValidationException
     */
    public function validateData(Order $order, array $data): bool
    {
        $validationResult = ValidationResult::create();
        $existingID =
            !empty($data[$this->addresstype . 'AddressID']) ? (int)$data[$this->addresstype . 'AddressID'] : 0;

        if ($existingID !== 0) {
            $member = Security::getCurrentUser();
            // If existing address selected, check that it exists in $member->AddressBook
            if (!$member || !$member->AddressBook()->byID($existingID)) {
                $validationResult->addError('Invalid address supplied', $this->addresstype . 'AddressID');
                throw ValidationException::create($validationResult);
            }
        } else {
            // Otherwise, require the normal address fields
            $required = parent::getRequiredFields($order);
            $addressLabels = singleton(\SilverShop\Model\Address::class)->fieldLabels(false);

            foreach ($required as $fieldName) {
                if (empty($data[$fieldName])) {
                    // attempt to get the translated field name
                    $fieldLabel = isset($addressLabels[$fieldName]) ? $addressLabels[$fieldName] : $fieldName;
                    $errorMessage = _t(
                        'SilverShop\Forms.FIELDISREQUIRED',
                        '{name} is required',
                        ['name' => $fieldLabel]
                    );

                    $validationResult->addError($errorMessage, $fieldName);
                    throw ValidationException::create($validationResult);
                }
            }
        }
        return true;
    }

    /**
     * Create a new address if the existing address has changed, or is not yet
     * created.
     *
     * @param  Order $order order to get addresses from
     * @param  array $data  data to set
     * @throws ValidationException
     */
    public function setData(Order $order, array $data): Order
    {
        $existingID =
            !empty($data[$this->addresstype . 'AddressID']) ? (int)$data[$this->addresstype . 'AddressID'] : 0;
        if ($existingID > 0) {
            $order->{$this->addresstype . 'AddressID'} = $existingID;
            $order->write();
            $order->extend('onSet' . $this->addresstype . 'Address', $address);
        } else {
            parent::setData($order, $data);
        }
        return $order;
    }

    /**
     * Provide translatable entities for this class
     */
    public function provideI18nEntities(): array
    {
        if ($this->addresstype !== '' && $this->addresstype !== '0') {
            return [

                "SilverShop\Model\Address.{$this->addresstype}Address" => [
                    "{$this->addresstype} Address",
                    "Label for the {$this->addresstype} address",
                ],
                "SilverShop\Model\Address.Existing{$this->addresstype}Address" => [
                    "Existing {$this->addresstype} Address",
                    "Label to select an existing {$this->addresstype} Address",
                ],
            ];
        }

        return [];
    }
}

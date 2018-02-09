<?php

namespace SilverShop\Model;

use SilverShop\ORM\FieldType\ShopCountry;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Address model using a generic format for storing international addresses.
 *
 * Typical Address Hierarcy:
 *    Continent
 *    Country
 *    State / Province / Territory (Island?)
 *    District / Suburb / County / City
 *        Code / Zip (may cross over the above)
 *    Street / Road - name + type: eg Gandalf Cresent
 *    (Premises/Building/Unit/Suite)
 *        (Floor/Level/Side/Wing)
 *    Number / Entrance / Room
 *    Person(s), Company, Department
 *
 * Collection of international address formats:
 *
 * @see http://bitboost.com/ref/international-address-formats.html
 *      xAL address standard:
 * @see https://www.oasis-open.org/committees/ciq/ciq.html#6
 *      Universal Postal Union addressing standards:
 * @see http://www.upu.int/nc/en/activities/addressing/standards.html
 *
 * @property ShopCountry $Country
 * @property string $State
 * @property string $City
 * @property string $PostalCode
 * @property string $Address
 * @property string $AddressLine2
 * @property string $Company
 * @property string $FirstName
 * @property string $Surname
 * @property string $Phone
 * @method   Member Member()
 * @method   Order[]|HasManyList ShippingAddressOrders()
 * @method   Order[]|HasManyList BillingAddressOrders()
 */
class Address extends DataObject
{
    private static $db = [
        'Country' => 'ShopCountry',
        //level1: Country = ISO 2-character country code
        'State' => 'Varchar(100)',
        //level2: Locality, Administrative Area, State, Province, Region, Territory, Island
        'City' => 'Varchar(100)',
        //level3: Dependent Locality, City, Suburb, County, District
        'PostalCode' => 'Varchar(20)',
        //code: ZipCode, PostCode (could cross above levels within a country)

        'Address' => 'Varchar(255)',
        //Number + type of thoroughfare/street. P.O. box
        'AddressLine2' => 'Varchar(255)',
        //Premises, Apartment, Building. Suite, Unit, Floor, Level, Side, Wing.

        'Company' => 'Varchar(100)',
        //Business, Organisation, Group, Institution.

        'FirstName' => 'Varchar(100)',
        //Individual, Person, Contact, Attention
        'Surname' => 'Varchar(100)',
        'Phone' => 'Varchar(100)',
    ];

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $has_many = [
        'ShippingAddressOrders' => Order::class . '.ShippingAddress',
        'BillingAddressOrders' => Order::class . '.BillingAddress'
    ];

    private static $casting = [
        'Country' => ShopCountry::class,
    ];

    private static $required_fields = [
        'Country',
        'State',
        'City',
        'Address',
    ];

    private static $summary_fields = [
        'toString' => 'Address',
    ];

    private static $table_name = 'SilverShop_Address';

    public function getCMSFields()
    {
        $self = $this;

        $this->beforeUpdateCMSFields(
            function (FieldList $fields) use ($self) {
                $fields->addFieldToTab(
                    'Root.Main',
                    $self->getCountryField(),
                    'State'
                );

                $fields->removeByName('MemberID');
            }
        );

        return parent::getCMSFields();
    }

    public function getFrontEndFields($params = null)
    {
        $fields = new FieldList(
            $this->getCountryField(),
            $addressfield = TextField::create('Address', $this->fieldLabel('Address')),
            $address2field =
                TextField::create('AddressLine2', $this->fieldLabel('AddressLine2')),
            $cityfield = TextField::create('City', $this->fieldLabel('City')),
            $statefield = TextField::create('State', $this->fieldLabel('State')),
            $postcodefield = TextField::create('PostalCode', $this->fieldLabel('PostalCode')),
            $phonefield = TextField::create('Phone', $this->fieldLabel('Phone'))
        );
        if (!empty($params['addfielddescriptions'])) {
            $addressfield->setDescription(
                _t(__CLASS__ . '.AddressHint', 'street / thoroughfare number, name, and type or P.O. Box')
            );
            $address2field->setDescription(
                _t(__CLASS__ . '.AddressLine2Hint', 'premises, building, apartment, unit, floor')
            );
            $cityfield->setDescription(_t(__CLASS__ . '.CityHint', 'or suburb, county, district'));
            $statefield->setDescription(_t(__CLASS__ . '.StateHint', 'or province, territory, island'));
        }

        $this->extend('updateFormFields', $fields);
        return $fields;
    }

    public function getCountryField()
    {
        $countries = SiteConfig::current_site_config()->getCountriesList();
        if (count($countries) == 1) {
            //field name is Country_readonly so it's value doesn't get updated
            return ReadonlyField::create(
                'Country_readonly',
                $this->fieldLabel('Country'),
                array_pop($countries)
            );
        }
        $field = DropdownField::create(
            'Country',
            $this->fieldLabel('Country'),
            $countries
        )->setHasEmptyDefault(true);

        $this->extend('updateCountryField', $field);

        return $field;
    }

    /**
     * Get an array of data fields that must be populated for model to be valid.
     * Required fields can be customised via self::$required_fields
     */
    public function getRequiredFields()
    {
        $fields = self::config()->required_fields;
        //hack to allow overriding arrays in ss config
        if (self::$required_fields != $fields) {
            foreach (self::$required_fields as $requirement) {
                if (($key = array_search($requirement, $fields)) !== false) {
                    unset($fields[$key]);
                }
            }
        }
        //set nicer keys for easier processing
        $fields = array_combine($fields, $fields);
        $this->extend('updateRequiredFields', $fields);
        //don't require country if shop config only specifies a single country
        if (isset($fields['Country']) && SiteConfig::current_site_config()->getSingleCountry()) {
            unset($fields['Country']);
        }

        return $fields;
    }

    /**
     * Get full name associated with this Address
     */
    public function getName()
    {
        return implode(
            ' ',
            array_filter(
                [
                    $this->FirstName,
                    $this->Surname,
                ]
            )
        );
    }

    /**
     * Convert address to a single string.
     */
    public function toString($separator = ', ')
    {
        $fields = array(
            $this->Company,
            $this->getName(),
            $this->Address,
            $this->AddressLine2,
            $this->City,
            $this->State,
            $this->PostalCode,
            $this->Country
        );
        $this->extend('updateToString', $fields);
        return implode($separator, array_filter($fields));
    }

    public function getTitle()
    {
        return $this->toString();
    }

    public function forTemplate()
    {
        return $this->renderWith(__CLASS__);
    }

    /**
     * Add alias setters for fields which are synonymous
     *
     * @param  string $val
     * @return $this
     */
    public function setProvince($val)
    {
        $this->State = $val;
        return $this;
    }

    public function setTerritory($val)
    {
        $this->State = $val;
        return $this;
    }

    public function setIsland($val)
    {
        $this->State = $val;
        return $this;
    }

    public function setPostCode($val)
    {
        $this->PostalCode = $val;
        return $this;
    }

    public function setZipCode($val)
    {
        $this->PostalCode = $val;
        return $this;
    }

    public function setStreet($val)
    {
        $this->Address = $val;
        return $this;
    }

    public function setStreet2($val)
    {
        $this->AddressLine2 = $val;
        return $this;
    }

    public function setAddress2($val)
    {
        $this->AddressLine2 = $val;
        return $this;
    }

    public function setInstitution($val)
    {
        $this->Company = $val;
        return $this;
    }

    public function setBusiness($val)
    {
        $this->Company = $val;
        return $this;
    }

    public function setOrganisation($val)
    {
        $this->Company = $val;
        return $this;
    }

    public function setOrganization($val)
    {
        $this->Company = $val;
        return $this;
    }

    function validate()
    {
        $result = parent::validate();

        foreach ($this->getRequiredFields() as $requirement) {
            if (empty($this->$requirement)) {
                $result->addError("Address Model validate function - missing required field: $requirement");
            }
        }

        return $result;
    }
}

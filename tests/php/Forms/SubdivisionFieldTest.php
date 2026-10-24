<?php

declare(strict_types=1);

namespace SilverShop\Tests\Forms;

use SilverShop\Forms\SubdivisionField;
use SilverShop\Model\Address;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\i18n\i18n;

final class SubdivisionFieldTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();
        i18n::set_locale('en_US');
    }

    public function testRendersDropdownForCountryWithSubdivisions(): void
    {
        $subdivisionField = SubdivisionField::create('State', 'State')
            ->setCountry('AU')
            ->setValue('New South Wales');

        $html = (string) $subdivisionField->Field();

        $this->assertStringContainsString('<select', $html);
        $this->assertMatchesRegularExpression('/<option value="NSW"\s+selected/', $html);
        $this->assertStringContainsString('data-country-field="Country"', $html);
        $this->assertStringContainsString('subdivision dropdown', $html);
    }

    public function testKeepsUnknownValueSelectable(): void
    {
        $html = (string) SubdivisionField::create('State', 'State')
            ->setCountry('AU')
            ->setValue('Somewhere Else')
            ->Field();

        $this->assertMatchesRegularExpression('/<option value="Somewhere Else"\s+selected/', $html);
    }

    public function testRendersTextInputWithoutSubdivisions(): void
    {
        $html = (string) SubdivisionField::create('State', 'State')
            ->setCountry('NZ')
            ->setValue('Wellington')
            ->Field();

        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('value="Wellington"', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testFollowsNamespacedCountryField(): void
    {
        $countryField = DropdownField::create('Country', 'Country', ['NZ' => 'New Zealand', 'US' => 'United States']);
        $subdivisionField = SubdivisionField::create('State', 'State')->setCountry('NZ');
        // checkout components prefix their field names
        $countryField->setName('ShippingAddress_Country');
        $subdivisionField->setName('ShippingAddress_State');

        $form = Form::create(Controller::curr(), 'TestForm', FieldList::create($countryField, $subdivisionField));
        $form->loadDataFrom(['ShippingAddress_Country' => 'US', 'ShippingAddress_State' => 'CA']);

        $this->assertSame('ShippingAddress_Country', $subdivisionField->getCountryFieldName());
        $this->assertSame('US', $subdivisionField->getCountry());
        $this->assertMatchesRegularExpression('/<option value="CA"\s+selected/', (string) $subdivisionField->Field());
    }

    public function testAddressUsesSubdivisionField(): void
    {
        $address = Address::create(['Country' => 'AU', 'State' => 'NSW']);

        $stateField = $address->getFrontEndFields()->dataFieldByName('State');
        $this->assertInstanceOf(SubdivisionField::class, $stateField);
        $this->assertSame('AU', $stateField->getCountry());

        $this->assertInstanceOf(SubdivisionField::class, $address->getCMSFields()->dataFieldByName('State'));
    }
}

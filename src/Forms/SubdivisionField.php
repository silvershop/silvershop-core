<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverShop\Model\AddressData;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;

/**
 * State / province / county field that follows a country field.
 *
 * Renders a dropdown of the country's subdivisions when they are known, and a
 * free-text input otherwise. Bundled JavaScript swaps between the two when the
 * country changes. Any string is accepted on submit, so addresses saved before
 * subdivisions were selectable keep their value.
 */
class SubdivisionField extends TextField
{
    /**
     * Endpoint returning subdivisions for a country, see WebServiceController.
     */
    private static string $subdivisions_url = 'api/v1/subdivisions';

    protected string $countryFieldName = 'Country';

    protected ?string $country = null;

    /**
     * Name this field was created with, before any namespacing (e.g. by
     * checkout components) was applied.
     */
    protected string $baseName;

    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        $this->baseName = (string) $name;
        parent::__construct($name, $title, $value, $maxLength, $form);
    }

    /**
     * Set the name of the country field this field follows, relative to any
     * prefix applied to this field's name.
     */
    public function setCountryFieldName(string $countryFieldName): static
    {
        $this->countryFieldName = $countryFieldName;
        return $this;
    }

    /**
     * Name of the country field, with the same prefix as this field's name.
     */
    public function getCountryFieldName(): string
    {
        $name = (string) $this->getName();
        $prefix = '';
        if ($this->baseName !== '' && str_ends_with($name, $this->baseName)) {
            $prefix = substr($name, 0, -strlen($this->baseName));
        }

        return $prefix . $this->countryFieldName;
    }

    /**
     * Set the country to use when the form has no country value.
     */
    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Country code from the form's country field, falling back to the
     * country set on this field, then the shop's single allowed country.
     */
    public function getCountry(): ?string
    {
        $form = $this->getForm();
        if ($form) {
            $countryField = $form->Fields()->dataFieldByName($this->getCountryFieldName());
            if ($countryField && $countryField->getValue()) {
                return (string) $countryField->getValue();
            }
        }

        if ($this->country) {
            return $this->country;
        }

        return SiteConfig::current_site_config()->getSingleCountry();
    }

    /**
     * Subdivisions of the current country, as code => name.
     *
     * @return array<string, string>
     */
    public function getSubdivisions(): array
    {
        return AddressData::singleton()->getSubdivisions($this->getCountry());
    }

    public function Type()
    {
        return 'subdivision text';
    }

    public function getAttributes()
    {
        return array_merge(
            parent::getAttributes(),
            [
                'data-subdivision-field' => true,
                'data-country-field' => $this->getCountryFieldName(),
                'data-subdivisions-url' => Controller::join_links(
                    Director::baseURL(),
                    static::config()->get('subdivisions_url')
                ),
            ]
        );
    }

    public function Field($properties = [])
    {
        Requirements::javascript('silvershop/core:client/dist/javascript/subdivision-field.js');

        $subdivisions = $this->getSubdivisions();
        if ($subdivisions === []) {
            return parent::Field($properties);
        }

        $value = (string) $this->getValue();
        if ($value !== '') {
            $value = AddressData::singleton()->findSubdivisionCode($this->getCountry(), $value) ?? $value;
            // keep values that don't match a known subdivision selectable
            if (!array_key_exists($value, $subdivisions)) {
                $subdivisions[$value] = $value;
            }
        }

        $dropdownField = DropdownField::create($this->getName(), $this->Title(), $subdivisions, $value)
            ->setHasEmptyDefault(true)
            ->setForm($this->getForm());

        $attributes = $this->getAttributes();
        unset($attributes['type'], $attributes['value'], $attributes['maxlength'], $attributes['size']);
        $classes = array_diff(explode(' ', (string) ($attributes['class'] ?? '')), ['', 'text']);
        $classes[] = 'dropdown';
        $attributes['class'] = implode(' ', $classes);
        foreach ($attributes as $name => $attributeValue) {
            $dropdownField->setAttribute($name, $attributeValue);
        }

        return $dropdownField->Field($properties);
    }
}

<?php

declare(strict_types=1);

namespace SilverShop\Tests\Extension;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * The shop's allowed countries are read from the stored `AllowedCountries` field, also when another extension on
 * SiteConfig adds a `getAllowedCountries()` method (which magic property access would otherwise return instead).
 */
class ShopConfigAllowedCountriesTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $required_extensions = [
        SiteConfig::class => [ShopConfigAllowedCountriesTest_GetterExtension::class],
    ];

    private function storeAllowedCountries(?string $json): SiteConfig
    {
        $config = SiteConfig::current_site_config();
        $config->setField('AllowedCountries', $json);
        $config->write();

        return $config;
    }

    public function testCountriesListUsesTheStoredCountries(): void
    {
        $config = $this->storeAllowedCountries('["NZ","AU"]');

        $this->assertSame(['AU', 'NZ'], $this->sortedKeys($config->getCountriesList()));
    }

    public function testNoStoredCountriesMeansEveryCountry(): void
    {
        $config = $this->storeAllowedCountries(null);

        $this->assertGreaterThan(200, count($config->getCountriesList()));
        $this->assertNull($config->getSingleCountry());
    }

    public function testSingleCountryUsesTheStoredCountry(): void
    {
        $config = $this->storeAllowedCountries('["NZ"]');

        $this->assertSame('NZ', $config->getSingleCountry());
    }

    public function testTheSettingsFieldShowsAndSavesTheStoredCountries(): void
    {
        $config = $this->storeAllowedCountries('["NZ","AU"]');
        $fields = $config->getCMSFields();
        $form = Form::create(null, 'EditForm', $fields, FieldList::create());
        $form->loadDataFrom($config);

        $field = $fields->dataFieldByName('AllowedCountries');
        $this->assertNotNull($field);
        $this->assertSame(['AU', 'NZ'], $this->sorted((array) $field->getValue()));
        $html = (string) $field->Field();
        $this->assertMatchesRegularExpression('/value="NZ"[^>]*checked/', $html, 'renders as a checkbox set');
        $this->assertDoesNotMatchRegularExpression('/value="DE"[^>]*checked/', $html);

        $field->setSubmittedValue(['NZ']);
        $field->saveInto($config);
        $this->assertSame('["NZ"]', $config->getField('AllowedCountries'));
    }

    /**
     * @param array<string, string> $list
     * @return string[]
     */
    private function sortedKeys(array $list): array
    {
        return $this->sorted(array_keys($list));
    }

    /**
     * @param array<int|string, string> $values
     * @return string[]
     */
    private function sorted(array $values): array
    {
        $values = array_values($values);
        sort($values);

        return $values;
    }
}

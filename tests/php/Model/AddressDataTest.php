<?php

declare(strict_types=1);

namespace SilverShop\Tests\Model;

use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\AddressData;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

final class AddressDataTest extends SapphireTest
{
    public function testCountries(): void
    {
        $countries = AddressData::singleton()->getCountries('en_NZ');

        $this->assertSame('New Zealand', $countries['NZ']);
        $this->assertSame('Australia', $countries['AU']);
        $this->assertSame('Australien', AddressData::singleton()->getCountries('de_DE')['AU']);
    }

    public function testCountryOverrides(): void
    {
        Config::modify()->set(ShopConfigExtension::class, 'iso_3166_country_codes', [
            'NZ' => 'Aotearoa New Zealand',
            'XX' => 'Testland',
        ]);

        $countries = ShopConfigExtension::get_all_countries();

        $this->assertSame('Aotearoa New Zealand', $countries['NZ']);
        $this->assertSame('Testland', $countries['XX']);
        $this->assertSame('Testland', ShopConfigExtension::countryCode2name('XX'));
        $this->assertArrayHasKey('AU', $countries);
    }

    public function testSubdivisions(): void
    {
        $addressData = AddressData::singleton();
        $australia = $addressData->getSubdivisions('AU', 'en');

        $this->assertSame('New South Wales', $australia['NSW']);
        $this->assertCount(8, $australia);
        $this->assertSame('California', $addressData->getSubdivisions('us', 'en')['CA']);

        // names are used as the postal value when no shorter code exists
        $this->assertSame('Hokkaido', $addressData->getSubdivisions('JP', 'en')['Hokkaido']);
        $this->assertSame('北海道', $addressData->getSubdivisions('JP', 'ja_JP')['Hokkaido']);
    }

    public function testNoSubdivisions(): void
    {
        $addressData = AddressData::singleton();

        $this->assertSame([], $addressData->getSubdivisions('NZ'));
        // Andorra has parishes, but its address format doesn't use them
        $this->assertSame([], $addressData->getSubdivisions('AD'));
        $this->assertSame([], $addressData->getSubdivisions(''));
        $this->assertSame([], $addressData->getSubdivisions(null));
        $this->assertSame([], $addressData->getSubdivisions('../AU'));
    }

    public function testFindSubdivisionCode(): void
    {
        $addressData = AddressData::singleton();

        $this->assertSame('NSW', $addressData->findSubdivisionCode('AU', 'new south wales'));
        $this->assertSame('NSW', $addressData->findSubdivisionCode('AU', ' nsw '));
        $this->assertSame('Hokkaido', $addressData->findSubdivisionCode('JP', '北海道'));
        $this->assertNull($addressData->findSubdivisionCode('AU', 'Wellington'));
        $this->assertNull($addressData->findSubdivisionCode('NZ', 'Wellington'));
        $this->assertNull($addressData->findSubdivisionCode('AU', ''));
    }
}

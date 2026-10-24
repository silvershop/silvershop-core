<?php

declare(strict_types=1);

namespace SilverShop\Model;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Locale;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\i18n\i18n;

/**
 * Country and subdivision (state / province / county) data, sourced from
 * commerceguys/addressing (CLDR and Google's address data).
 */
class AddressData
{
    use Injectable;

    private ?CountryRepository $countryRepository = null;

    private ?SubdivisionRepository $subdivisionRepository = null;

    private ?AddressFormatRepository $addressFormatRepository = null;

    /**
     * Get all countries as ISO 3166-1 alpha-2 code => localised name.
     *
     * @return array<string, string>
     */
    public function getCountries(?string $locale = null): array
    {
        return $this->getCountryRepository()->getList($locale ?? i18n::get_locale());
    }

    /**
     * Get the subdivisions (state, province, county, etc.) of a country as
     * postal code => localised name. The postal code is the value written on
     * an address, e.g. "NSW" for New South Wales or "CA" for California.
     *
     * Empty when the country has no predefined subdivisions, or its address
     * format doesn't use one.
     *
     * @return array<string, string>
     */
    public function getSubdivisions(?string $countryCode, ?string $locale = null): array
    {
        $countryCode = strtoupper(trim((string) $countryCode));
        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1 || !$this->usesAdministrativeArea($countryCode)) {
            return [];
        }

        $locale ??= i18n::get_locale();
        $subdivisions = [];
        foreach ($this->getSubdivisionRepository()->getAll([$countryCode]) as $subdivision) {
            $useLocalName = $subdivision->getLocalName() !== null
                && Locale::matchCandidates($locale, $subdivision->getLocale());
            $subdivisions[$subdivision->getCode()] = $useLocalName
                ? (string) $subdivision->getLocalName()
                : $subdivision->getName();
        }

        return $subdivisions;
    }

    /**
     * Find the subdivision code matching a free-text value, comparing against
     * codes and names case-insensitively. Useful for existing addresses that
     * were entered before subdivisions were selectable.
     */
    public function findSubdivisionCode(?string $countryCode, ?string $value): ?string
    {
        $needle = mb_strtolower(trim((string) $value));
        if ($needle === '') {
            return null;
        }

        $countryCode = strtoupper(trim((string) $countryCode));
        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1 || !$this->usesAdministrativeArea($countryCode)) {
            return null;
        }

        foreach ($this->getSubdivisionRepository()->getAll([$countryCode]) as $subdivision) {
            $candidates = [
                $subdivision->getCode(),
                $subdivision->getName(),
                $subdivision->getLocalCode(),
                $subdivision->getLocalName(),
            ];
            foreach ($candidates as $candidate) {
                if ($candidate !== null && mb_strtolower($candidate) === $needle) {
                    return $subdivision->getCode();
                }
            }
        }

        return null;
    }

    /**
     * Whether addresses in the given country have a state / province line.
     */
    public function usesAdministrativeArea(string $countryCode): bool
    {
        $addressFormat = $this->getAddressFormatRepository()->get($countryCode);

        return in_array(AddressField::ADMINISTRATIVE_AREA, $addressFormat->getUsedFields(), true);
    }

    public function getCountryRepository(): CountryRepository
    {
        return $this->countryRepository ??= new CountryRepository();
    }

    public function getSubdivisionRepository(): SubdivisionRepository
    {
        return $this->subdivisionRepository ??= new SubdivisionRepository($this->getAddressFormatRepository());
    }

    public function getAddressFormatRepository(): AddressFormatRepository
    {
        return $this->addressFormatRepository ??= new AddressFormatRepository();
    }
}

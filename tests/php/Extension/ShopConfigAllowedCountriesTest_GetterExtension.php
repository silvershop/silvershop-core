<?php

declare(strict_types=1);

namespace SilverShop\Tests\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Stands in for another module that adds a `getAllowedCountries()` method to SiteConfig, e.g.
 * symbiote/silverstripe-addressable's Addressable extension, which returns an ISO code => name list for its own
 * address form. A getter takes precedence over the database field of the same name on magic property access
 * (`$siteConfig->AllowedCountries`), so it shadows the shop's stored `AllowedCountries` JSON.
 *
 * @extends Extension<static>
 */
class ShopConfigAllowedCountriesTest_GetterExtension extends Extension implements TestOnly
{
    /**
     * @return array<string, string>
     */
    public function getAllowedCountries(): array
    {
        return ['NL' => 'Netherlands', 'BE' => 'Belgium', 'DE' => 'Germany'];
    }
}

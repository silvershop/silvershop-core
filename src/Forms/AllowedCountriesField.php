<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;

/**
 * The allowed ordering and shipping countries on Settings > Shop.
 *
 * Loads the stored JSON with getField() instead of magic property access, so a `getAllowedCountries()` method that
 * another extension adds to SiteConfig (e.g. symbiote/silverstripe-addressable) can't take its place.
 */
class AllowedCountriesField extends CheckboxSetField
{
    public function loadFrom(DataObjectInterface $record)
    {
        $name = $this->getName();
        if ($record instanceof DataObject && $name !== '' && $record->hasField($name)) {
            $this->setValue($this->stringDecode($record->getField($name)));
            return;
        }

        parent::loadFrom($record);
    }
}

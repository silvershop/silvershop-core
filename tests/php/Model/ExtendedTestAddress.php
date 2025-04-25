<?php

namespace SilverShop\Tests\Model;

use SilverShop\Model\Address;
use SilverStripe\Dev\TestOnly;

class ExtendedTestAddress extends Address implements TestOnly
{
    private static string $table_name = 'SilverShop_ExtendedTestAddress';

    // Addd postal code to required fields
    public function getRequiredFields(): array
    {
        $fields = parent::getRequiredFields();
        $fields['PostalCode'] = 'PostalCode';
        return $fields;
    }
}

<?php

namespace SilverShop\Tests\Model;

use SilverShop\Model\Address;
use SilverStripe\Dev\TestOnly;

class ExtendedTestAddress extends Address implements TestOnly
{
    // Addd postal code to required fields
    public function getRequiredFields()
    {
        $fields = parent::getRequiredFields();
        $fields['PostalCode'] = 'PostalCode';
        return $fields;
    }
}

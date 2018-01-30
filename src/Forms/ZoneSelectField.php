<?php

namespace SilverShop\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;

class ZoneSelectField extends DropdownField
{
    public function getSource()
    {
        $zones = DataObject::get('Zone');
        if ($zones && $zones->exists()) {
            return ['' => $this->emptyString] + $zones->map('ID', 'Name');
        }
        return [];
    }
}

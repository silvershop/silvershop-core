<?php

namespace SilverShop\Forms;

use SilverShop\Model\Zone;
use SilverStripe\Forms\DropdownField;

class ZoneSelectField extends DropdownField
{
    public function getSource()
    {
        $zones = Zone::get();
        if ($zones && $zones->exists()) {
            return ['' => $this->emptyString] + $zones->map('ID', 'Name');
        }
        return [];
    }
}

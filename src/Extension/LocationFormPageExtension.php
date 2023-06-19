<?php

namespace SilverShop\Extension;

use SilverShop\Forms\SetLocationForm;
use SilverStripe\Core\Extension;

class LocationFormPageExtension extends Extension
{
    private static $allowed_actions = [
        'SetLocationForm',
    ];

    public function SetLocationForm()
    {
        return SetLocationForm::create($this->owner);
    }
}

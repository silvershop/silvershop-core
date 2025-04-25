<?php

namespace SilverShop\Extension;

use SilverShop\Forms\SetLocationForm;
use SilverStripe\Core\Extension;

class LocationFormPageExtension extends Extension
{
    private static array $allowed_actions = [
        'SetLocationForm',
    ];

    public function SetLocationForm(): SetLocationForm
    {
        return SetLocationForm::create($this->owner);
    }
}

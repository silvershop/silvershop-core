<?php

namespace SilverShop\Core\Address;

use SilverStripe\Core\Extension;

class LocationFormPageDecorator extends Extension
{
    private static $allowed_actions = array(
        'SetLocationForm',
    );

    public function SetLocationForm()
    {
        return SetLocationForm::create($this->owner);
    }
}

<?php

declare(strict_types=1);

namespace SilverShop\Extension;

use SilverShop\Forms\SetLocationForm;
use SilverStripe\Core\Extension;

/**
 * @extends Extension<static>
 */
class LocationFormPageExtension extends Extension
{
    private static array $allowed_actions = [
        'SetLocationForm',
    ];

    public function SetLocationForm(): SetLocationForm
    {
        return SetLocationForm::create($this->getOwner());
    }
}

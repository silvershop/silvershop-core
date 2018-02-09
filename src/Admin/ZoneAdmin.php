<?php

namespace SilverShop\Admin;

use SilverShop\Model\Zone;
use SilverStripe\Admin\ModelAdmin;

class ZoneAdmin extends ModelAdmin
{
    private static $menu_title = 'Zones';

    private static $url_segment = 'zones';

    private static $menu_icon_class = 'silvershop-icon-zones';

    private static $menu_priority = 2;

    private static $managed_models = [
        Zone::class,
    ];
}

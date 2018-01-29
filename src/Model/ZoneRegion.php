<?php

namespace SilverShop\Core\Model;


class ZoneRegion extends RegionRestriction
{
    private static $has_one = [
        'Zone' => Zone::class
    ];

    private static $table_name = 'SilverShop_ZoneRegion';
}

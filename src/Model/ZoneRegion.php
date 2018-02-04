<?php

namespace SilverShop\Model;

/**
 * Class ZoneRegion
 *
 * @property int $ZoneID
 * @method   Zone Zone()
 */
class ZoneRegion extends RegionRestriction
{
    private static $has_one = [
        'Zone' => Zone::class
    ];

    private static $table_name = 'SilverShop_ZoneRegion';
}

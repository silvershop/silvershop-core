<?php

namespace SilverShop\Tests\Model;


use SilverShop\Model\RegionRestriction;
use SilverStripe\Dev\TestOnly;

class RegionRestriction_RateTest extends RegionRestriction implements TestOnly
{
    private static $db = array(
        'Rate' => 'Currency',
    );
}

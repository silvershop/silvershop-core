<?php

namespace SilverShop\Tests\Page;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataExtension;

class ProductTest_FractionalDiscountExtension extends DataExtension implements TestOnly
{
    public function updateSellingPrice(&$price)
    {
        $price -= 0.015;
    }
}

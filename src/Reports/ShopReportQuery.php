<?php

namespace SilverShop\Reports;

use SilverStripe\ORM\Queries\SQLSelect;

class ShopReportQuery extends SQLSelect
{
    public function canSortBy($fieldName)
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace SilverShop\Reports;

use SilverStripe\ORM\Queries\SQLSelect;

class ShopReportQuery extends SQLSelect
{
    public function canSortBy($fieldName): bool
    {
        return true;
    }
}

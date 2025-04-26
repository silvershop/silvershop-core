<?php

namespace SilverShop\Reports;

use SilverShop\Model\Order;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Report on the number of abandoned carts.
 *
 * @date       09.24.2014
 * @package    shop
 * @subpackage reports
 */
class AbandonedCartReport extends ShopPeriodReport
{
    protected $title = 'Abandoned Carts';

    protected $description = 'Monitor abandoned carts for a particular period. Group results by year, month, or day.';

    protected $dataClass = Order::class;

    protected $periodfield = '"SilverShop_Order"."Created"';

    protected $grouping = true;

    public function columns(): array
    {
        $period = isset($_GET['filters']['Grouping']) ? $_GET['filters']['Grouping'] : 'Month';
        return [
            'FilterPeriod' => $period,
            'Count' => 'Count',
            'TotalValue' => 'Total Value',
        ];
    }

    public function query($params): ShopReportQuery|SQLSelect
    {
        return parent::query($params)
            ->selectField('COUNT("SilverShop_Order"."ID")', 'Count')
            ->selectField('SUM("SilverShop_Order"."Total")', 'TotalValue')
            ->addWhere(['"Status" = ?' => 'Cart']);
    }
}

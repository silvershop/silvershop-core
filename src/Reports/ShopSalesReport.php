<?php

namespace SilverShop\Reports;

use SilverShop\Model\Order;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Order sales for the entire shop.
 *
 * @todo: exclude some records: cancelled, refunded, etc
 * @todo: include a graph
 * @todo: count products sold
 * @todo: show geographical map of sales
 * @todo: add profits
 */
class ShopSalesReport extends ShopPeriodReport
{
    protected $title = 'Shop Sales';

    protected $description = 'Monitor shop sales performance for a particular period. Group results by year, month, or day.';

    protected $dataClass = Order::class;

    protected $periodfield = '"SilverShop_Order"."Paid"';

    protected $grouping = true;

    public function columns(): array
    {
        $period = isset($_GET['filters']['Grouping']) ? $_GET['filters']['Grouping'] : 'Month';
        return [
            'FilterPeriod' => $period,
            'Count' => 'Order Count',
            'Sales' => 'Total Sales',
        ];
    }

    public function query($params): ShopReportQuery|SQLSelect
    {
        return parent::query($params)
            ->selectField('COUNT("SilverShop_Order"."ID")', 'Count')
            ->selectField('SUM("SilverShop_Order"."Total")', 'Sales');
    }
}

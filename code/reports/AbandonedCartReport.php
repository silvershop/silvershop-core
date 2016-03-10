<?php

/**
 * Report on the number of abandoned carts.
 *
 * @date       09.24.2014
 * @package    shop
 * @subpackage reports
 */
class AbandonedCartReport extends ShopPeriodReport
{
    protected $title       = "Abandoned Carts";

    protected $description = "Monitor abandoned carts for a particular period. Group results by year, month, or day.";

    protected $dataClass   = "Order";

    protected $periodfield = "\"Order\".\"Created\"";

    protected $grouping    = true;

    public function columns()
    {
        $period = isset($_GET['filters']['Grouping']) ? $_GET['filters']['Grouping'] : "Month";
        return array(
            "FilterPeriod" => $period,
            "Count"        => "Count",
            "TotalValue"   => "Total Value",
        );
    }

    public function query($params)
    {
        return parent::query($params)
            ->selectField("Count(\"Order\".\"ID\")", "Count")
            ->selectField("Sum(\"Order\".\"Total\")", "TotalValue")
            ->addWhere("\"Status\" = 'Cart'");
    }
}

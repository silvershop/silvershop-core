<?php

/**
 * Tax report
 *
 * @date       09.24.2014
 * @package    shop
 * @subpackage reports
 */
class TaxReport extends ShopPeriodReport
{
    protected $title       = "Tax";

    protected $description = "Report tax charged on orders. Only includes orders that have been paid.";

    protected $dataClass   = "Order";

    protected $periodfield = "\"Order\".\"Paid\"";

    protected $grouping    = true;

    public function columns()
    {
        $period = isset($_GET['filters']['Grouping']) ? $_GET['filters']['Grouping'] : "Month";
        return array(
            "FilterPeriod" => $period,
            "Count"        => "Order Count",
            "Sales"        => "Total Sales",
            "Tax"          => "Total Tax",
        );
    }

    public function query($params)
    {
        return parent::query($params)
            ->addInnerJoin(
                "OrderAttribute",
                "OrderAttribute.OrderID = Order.ID AND OrderAttribute.ClassName like '%TaxModifier'"
            )
            ->addInnerJoin("OrderModifier", "OrderModifier.ID = OrderAttribute.ID")
            ->selectField("Count(\"Order\".\"ID\")", "Count")
            ->selectField("Sum(\"OrderModifier\".\"Amount\")", "Tax")
            ->selectField("Sum(\"Order\".\"Total\")", "Sales");
    }
}

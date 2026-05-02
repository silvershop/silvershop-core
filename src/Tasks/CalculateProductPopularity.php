<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class CalculateProductPopularity extends BuildTask
{
    protected string $title = 'Calculate Product Sales Popularity';

    protected static string $description = 'Count up total sales quantities for each product';

    private static string $number_sold_calculation_type = 'SUM'; //SUM or COUNT

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $this->viasql();
        $output->writeln('Product sales counts updated');
        return Command::SUCCESS;
    }

    /**
     * Update both live and stage tables, based on the algorithm:
     *    product popularity = sum(1/order_age) * sum(item_quantity)
     */
    public function viasql(): void
    {
        foreach (['_Live', ''] as $stage) {
            $sql = <<<SQL
UPDATE "SilverShop_Product{$stage}" SET "Popularity" = (
  SELECT
    SUM(1 / (DATEDIFF(NOW(),"SilverShop_Order"."Paid")+1)) * SUM("SilverShop_OrderItem"."Quantity")
    AS Popularity
  FROM "SiteTree{$stage}"
    INNER JOIN "SilverShop_Product_OrderItem" ON "SiteTree{$stage}"."ID" = "SilverShop_Product_OrderItem"."ProductID"
    INNER JOIN "SilverShop_OrderItem" ON "SilverShop_OrderItem"."ID" = "SilverShop_Product_OrderItem"."ID"
    INNER JOIN "SilverShop_OrderAttribute" ON "SilverShop_OrderItem"."ID" = "SilverShop_OrderAttribute"."ID"
    INNER JOIN "SilverShop_Order" ON "SilverShop_OrderAttribute"."OrderID" = "SilverShop_Order"."ID"
  WHERE "SiteTree{$stage}"."ID" = "SilverShop_Product{$stage}"."ID"
    AND "SilverShop_Order"."Paid" IS NOT NULL
  GROUP BY "SilverShop_Product{$stage}"."ID"
);
SQL;
            DB::query($sql);
        }
    }
}

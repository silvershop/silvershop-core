<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverShop\Model\Order;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Recalculate All Orders
 * Re-runs all calculation functions on all orders so that database is populated with pre-calculated values.
 *
 * @subpackage tasks
 */
class RecalculateAllOrdersTask extends BuildTask
{
    protected string $title = 'Recalculate All Orders';

    protected static string $description = 'Runs all price calculation functions on all orders.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        //TODO: include order total calculation, once that gets written
        //TODO: figure out how to make this run faster
        //TODO: better memory managment...the destroy calls are not enough it appears.

        if ($orders = Order::get()) {
            $output->writeln('Writing all order items...');
            foreach ($orders as $order) {
                $order->calculate();
                $order->write();
            }

            $output->writeln('Done.');
        }
        return Command::SUCCESS;
    }
}
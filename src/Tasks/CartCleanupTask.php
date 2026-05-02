<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use LogicException;
use SilverShop\Model\Order;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Cart Cleanup Task.
 *
 * Removes all orders (carts) that are older than a specific time offset.
 *
 * @package    shop
 * @subpackage tasks
 */
class CartCleanupTask extends BuildTask
{
    private static int $delete_after_mins = 120;

    /**
     * @var string
     */
    protected string $title = 'Delete abandoned carts';

    /**
     * @var string
     */
    protected static string $description = 'Deletes abandoned carts.';

    private PolyOutput $output;

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $this->output = $output;

        if (!$this->config()->get('delete_after_mins')) {
            throw new LogicException('No valid time specified in "delete_after_mins"');
        }

        $count = 0;
        $time = date('Y-m-d H:i:s', DBDatetime::now()->getTimestamp() - $this->config()->get('delete_after_mins') * 60);

        $this->log('Deleting all orders since ' . $time);

        $dataList = Order::get()->filter(
            [
                'Status' => 'Cart',
                'LastEdited:LessThan' => $time,
            ]
        );
        foreach ($dataList as $order) {
            $this->log(sprintf('Deleting order #%s (Reference: %s)', $order->ID, $order->Reference));
            $order->delete();
            $order->destroy();
            ++$count;
        }

        $this->log($count . ' old carts removed.');
        return Command::SUCCESS;
    }

    protected function log(string $msg): void
    {
        $this->output->writeln($msg);
    }
}

<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Email\Email;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * ShopEmailPreviewTask
 *
 * @author     Anselm Christophersen <ac@anselm.dk>
 * @date       September 2016
 * @package    shop
 * @subpackage tasks
 */
class ShopEmailPreviewTask extends BuildTask
{
    protected string $title = 'Preview Shop Emails';

    protected static string $description = 'Previews shop emails';

    /**
     * @var list<string>
     */
    protected array $previewableEmails = [
        'Confirmation',
        'Receipt',
        'AdminNotification',
        'CancelNotification',
        'StatusChange',
        'OrderStatusLog',
    ];

    public function getOptions(): array
    {
        return [
            new InputOption(
                'email',
                null,
                InputOption::VALUE_OPTIONAL,
                'The email type to preview. Available: ' . implode(', ', $this->previewableEmails),
            ),
            new InputOption(
                'order-status-log',
                null,
                InputOption::VALUE_OPTIONAL,
                'SilverShop_OrderStatusLog ID to preview (optional). Only used when --email=OrderStatusLog; '
                    . 'defaults to the latest customer-visible log for the preview order.',
            ),
        ];
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $email = $input->getOption('email');

        $output->writeln('Available email previews:');
        foreach ($this->previewableEmails as $method) {
            $output->writeln('  - ' . $method);
        }
        $output->writeln('');

        if ($email && in_array($email, $this->previewableEmails, true)) {
            $order = Order::get()->filter('Email:not', ['', null])->first()
                ?? Order::get()->first();

            if (!$order) {
                $output->writeln('No orders found to preview email with.');
                return Command::FAILURE;
            }

            // Ensure a valid email exists on the order for preview rendering
            if (!$order->getLatestEmail()) {
                $order->Email = Email::config()->admin_email ?: 'preview@example.com';
            }

            $notifier = OrderEmailNotifier::create($order);
            $notifier->setDebugMode(true);

            if ($email === 'StatusChange') {
                $output->writeForHtml($notifier->sendStatusChange('This is a test title', 'This is a test note'));
            } elseif ($email === 'OrderStatusLog') {
                $logOption = $input->getOption('order-status-log');
                $usedExplicitId = false;
                $log = $this->resolvePreviewOrderStatusLog($order, $logOption, $usedExplicitId);

                if (!$log) {
                    if ($usedExplicitId) {
                        $output->writeln(
                            'Invalid order-status-log ID, or that log does not belong to the preview order.'
                        );
                    } else {
                        $output->writeln(
                            'No suitable OrderStatusLog found for this order. Create a visible log entry in the CMS, '
                                . 'or pass --order-status-log=<ID> for a log that belongs to the preview order.'
                        );
                    }

                    return Command::FAILURE;
                }

                $output->writeln(sprintf('Preview using OrderStatusLog #%d (%s)', $log->ID, $log->Title ?: '(no title)'));
                $output->writeForHtml($notifier->sendStatusChange($log->Title ?: 'Order Update', (string) $log->Note));
            } else {
                $method = 'send' . $email;
                $output->writeForHtml($notifier->$method());
            }
            $output->writeForAnsi('Email rendered (HTML only - open in browser to preview).');
        } elseif ($email) {
            $output->writeln('Unknown email type: ' . $email);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param mixed $logOption Raw value from {@link InputInterface::getOption()}
     */
    protected function resolvePreviewOrderStatusLog(Order $order, mixed $logOption, bool &$usedExplicitId): ?OrderStatusLog
    {
        $usedExplicitId = false;

        if ($logOption !== null && $logOption !== '' && $logOption !== false) {
            $usedExplicitId = true;
            $id = (int) $logOption;
            if ($id < 1) {
                return null;
            }

            $log = OrderStatusLog::get()->byID($id);
            if (!$log || (int) $log->OrderID !== (int) $order->ID) {
                return null;
            }

            return $log;
        }

        $visible = OrderStatusLog::get()
            ->filter([
                'OrderID' => $order->ID,
                'VisibleToCustomer' => true,
            ])
            ->sort('"Created" DESC')
            ->first();

        if ($visible) {
            return $visible;
        }

        return OrderStatusLog::get()
            ->filter('OrderID', $order->ID)
            ->sort('"Created" DESC')
            ->first();
    }
}

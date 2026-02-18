<?php

declare(strict_types=1);

namespace SilverShop\Tasks;

use SilverShop\Checkout\OrderEmailNotifier;
use SilverShop\Model\Order;
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

    protected $previewableEmails = [
        'Confirmation',
        'Receipt',
        'AdminNotification',
        'CancelNotification',
        'StatusChange'
    ];

    public function getOptions(): array
    {
        return [
            new InputOption(
                'email',
                null,
                InputOption::VALUE_OPTIONAL,
                'The email type to preview. Available: Confirmation, Receipt, AdminNotification, CancelNotification, StatusChange',
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

        if ($email && in_array($email, $this->previewableEmails)) {
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

            $method = 'send' . $email;

            if ($email === 'StatusChange') {
                $output->writeForHtml($notifier->$method('This is a test title', 'This is a test note'));
            } else {
                $output->writeForHtml($notifier->$method());
            }
            $output->writeForAnsi('Email rendered (HTML only - open in browser to preview).');
        } elseif ($email) {
            $output->writeln('Unknown email type: ' . $email);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
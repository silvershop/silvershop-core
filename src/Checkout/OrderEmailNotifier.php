<?php

namespace SilverShop\Checkout;

use Psr\Log\LoggerInterface;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverShop\Page\CheckoutPage;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Handles email notifications to customers and / or admins.
 *
 * @package shop
 */
class OrderEmailNotifier
{
    use Injectable;
    use Configurable;

    /**
     * BCC Confirmation Emails to Admin
     */
    private static bool $bcc_confirmation_to_admin = false;

    /**
     * BCC Receipt Emails to Admin
     */
    private static bool $bcc_receipt_to_admin = false;

    /**
     * BCC Status Change Emails to Admin
     */
    private static bool $bcc_status_change_to_admin = false;

    private static array $dependencies = [
        'Logger' => '%$' . LoggerInterface::class,
    ];

    protected LoggerInterface $logger;

    protected Order $order;

    protected bool $debugMode = false;

    /**
     * Assign the order to a local variable
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function setDebugMode(bool $bool): static
    {
        $this->debugMode = $bool;
        return $this;
    }

    protected function buildEmail(string $template, string $subject): Email
    {
        $from = ShopConfigExtension::config()->email_from ? ShopConfigExtension::config()->email_from : Email::config()->admin_email;
        $to = $this->order->getLatestEmail();
        $checkoutpage = CheckoutPage::get()->first();
        $completemessage = $checkoutpage ? $checkoutpage->dbObject('PurchaseComplete') : '';

        /**
         * @var Email $email
         */
        $email = Email::create()
            ->setHTMLTemplate($template)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject);

        $email->setData(
            [
                'PurchaseCompleteMessage' => $completemessage,
                'Order' => $this->order,
                'BaseURL' => Director::absoluteBaseURL(),
            ]
        );

        return $email;
    }

    /**
     * Send a mail of the order to the client (and another to the admin).
     *
     * @param string $template    - the class name of the email you wish to send
     * @param string $subject     - subject of the email
     * @param bool   $copyToAdmin - true by default, whether it should send a copy to the admin
     */
    public function sendEmail(string $template, string $subject, $copyToAdmin = true): bool|string
    {
        $email = $this->buildEmail($template, $subject);

        if ($copyToAdmin) {
            $email->setBCC(Email::config()->admin_email);
        }
        if ($this->debugMode) {
            return $this->debug($email);
        } else {
            try {
                $email->send();
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('OrderEmailNotifier.sendEmail: error sending email in ' . __FILE__ . ' line ' . __LINE__ . ": {$e->getMessage()}");
                return false;
            }
            return true;
        }
    }

    /**
     * Send customer a confirmation that the order has been received
     */
    public function sendConfirmation(): bool|string
    {
        $subject = _t(
            'SilverShop\ShopEmail.ConfirmationSubject',
            'Order #{OrderNo} confirmation',
            '',
            ['OrderNo' => $this->order->Reference]
        );
        return $this->sendEmail(
            'SilverShop/Model/Order_ConfirmationEmail',
            $subject,
            self::config()->bcc_confirmation_to_admin
        );
    }

    /**
     * Notify store owner about new order.
     */
    public function sendAdminNotification(): bool|string
    {
        $subject = _t(
            'SilverShop\ShopEmail.AdminNotificationSubject',
            'Order #{OrderNo} notification',
            '',
            ['OrderNo' => $this->order->Reference]
        );

        $email = $this->buildEmail('SilverShop/Model/Order_AdminNotificationEmail', $subject)
            ->setTo(Email::config()->admin_email);

        if ($this->debugMode) {
            return $this->debug($email);
        } else {
            try {
                $email->send();
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('OrderEmailNotifier.sendAdminNotification: error sending email in ' . __FILE__ . ' line ' . __LINE__ . ": {$e->getMessage()}");
                return false;
            }
            return true;
        }
    }

    /**
     * Send customer an order receipt email.
     * Precondition: The order payment has been successful
     */
    public function sendReceipt(): bool|string
    {
        $subject = _t(
            'SilverShop\ShopEmail.ReceiptSubject',
            'Order #{OrderNo} receipt',
            '',
            ['OrderNo' => $this->order->Reference]
        );

        return $this->sendEmail(
            'SilverShop/Model/Order_ReceiptEmail',
            $subject,
            self::config()->bcc_receipt_to_admin
        );
    }

    /**
     * Sends an email to the admin that an order has been cancelled
     */
    public function sendCancelNotification(): bool|string
    {
        $email = Email::create()
            ->setSubject(_t(
                'SilverShop\ShopEmail.CancelSubject',
                'Order #{OrderNo} cancelled by member',
                '',
                ['OrderNo' => $this->order->Reference]
            ))
            ->setFrom(Email::config()->admin_email)
            ->setTo(Email::config()->admin_email)
            ->setBody($this->order->renderWith(Order::class));

        if ($this->debugMode) {
            return $this->debug($email);
        } else {
            try {
                $email->send();
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('OrderEmailNotifier.sendCancelNotification: error sending email in ' . __FILE__ . ' line ' . __LINE__ . ": {$e->getMessage()}");
                return false;
            }
            return true;
        }
    }

    /**
     * Send an email to the customer containing the latest note of {@link OrderStatusLog} and the current status.
     *
     * @param string $title Subject for email
     * @param string $note  Optional note-content (instead of using the OrderStatusLog)
     */
    public function sendStatusChange(string $title, string $note = ''): bool|string
    {
        $latestLog = null;

        if (!$note) {
            // Find the latest log message that hasn't been sent to the client yet, but can be (e.g. is visible)
            $latestLog = OrderStatusLog::get()
                ->filter("OrderID", $this->order->ID)
                ->filter("SentToCustomer", 0)
                ->filter("VisibleToCustomer", 1)
                ->first();

            if ($latestLog) {
                $note = $latestLog->Note;
                $title = $latestLog->Title;
            }
        }

        if (Config::inst()->get(OrderProcessor::class, 'receipt_email')) {
            $adminEmail = Config::inst()->get(OrderProcessor::class, 'receipt_email');
        } else {
            $adminEmail = Email::config()->admin_email;
        }

        $email = Email::create()
            ->setFrom($adminEmail)
            ->setSubject(_t('SilverShop\ShopEmail.StatusChangeSubject', 'SilverShop – {Title}', ['Title' => $title]))
            ->setTo($this->order->getLatestEmail())
            ->setHTMLTemplate('SilverShop/Model/Order_StatusEmail')
            ->setData(
                [
                    'Order' => $this->order,
                    'Note' => $note,
                    'FromEmail' => $adminEmail
                ]
            );

        if (self::config()->bcc_status_change_to_admin) {
            $email->setBCC(Email::config()->admin_email);
        }

        if ($this->debugMode) {
            $result = $this->debug($email);
        } else {
            try {
                $email->send();
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('OrderEmailNotifier.sendStatusChange: error sending email in ' . __FILE__ . ' line ' . __LINE__ . ": {$e->getMessage()}");
                return false;
            }
            return true;
        }

        if ($latestLog) {
            // If we got the note from an OrderStatusLog object, mark it as having been sent to the customer
            $latestLog->SentToCustomer = true;
            $latestLog->write();
        }

        return $result;
    }

    /**
     * The new Email::debug method in SilverStripe dumps the entire message with all message parts,
     * which makes it unusable to preview an Email.
     * This method simulates the old way of the message output and renders only the HTML body.
     */
    protected function debug(Email $email): string
    {
        $htmlTemplate = $email->getHTMLTemplate();
        $htmlRender = $email->getData()->renderWith($htmlTemplate)->RAW();
        $template = $email->getHTMLTemplate();
        $headers = $email->getHeaders()->toString();
        return "<h2>Email HTML template: $template</h2>\n" .
            "<h3>Headers</h3>" .
            "<pre>$headers</pre>" .
            "<h3>Body</h3>" .
            $htmlRender;
    }

    /**
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }
}

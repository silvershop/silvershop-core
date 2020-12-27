<?php

namespace SilverShop\Checkout;

use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverShop\Page\CheckoutPage;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;

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
     * @var Order $order
     */
    protected $order;

    /**
     * @var bool
     */
    protected $debugMode = false;

    /**
     * Assign the order to a local variable
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function setDebugMode($bool)
    {
        $this->debugMode = $bool;
        return $this;
    }

    /**
     * @param string $template
     * @param string $subject
     *
     * @return Email
     */
    protected function buildEmail($template, $subject)
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
     *
     * @return bool|string
     */
    public function sendEmail($template, $subject, $copyToAdmin = true)
    {
        $email = $this->buildEmail($template, $subject);

        if ($copyToAdmin) {
            $email->setBcc(Email::config()->admin_email);
        }
        if ($this->debugMode) {
            return $this->debug($email);
        } else {
            return $email->send();
        }
    }

    /**
     * Send customer a confirmation that the order has been received
     *
     * @return bool
     */
    public function sendConfirmation()
    {
        $subject = _t(
            'SilverShop\ShopEmail.ConfirmationSubject',
            'Order #{OrderNo} confirmation',
            '',
            array('OrderNo' => $this->order->Reference)
        );
        return $this->sendEmail(
            'SilverShop/Model/Order_ConfirmationEmail',
            $subject,
            self::config()->bcc_confirmation_to_admin
        );
    }

    /**
     * Notify store owner about new order.
     *
     * @return bool|string
     */
    public function sendAdminNotification()
    {
        $subject = _t(
            'SilverShop\ShopEmail.AdminNotificationSubject',
            'Order #{OrderNo} notification',
            '',
            array('OrderNo' => $this->order->Reference)
        );

        $email = $this->buildEmail('SilverShop/Model/Order_AdminNotificationEmail', $subject)
            ->setTo(Email::config()->admin_email);

        if ($this->debugMode) {
            return $this->debug($email);
        } else {
            return $email->send();
        }
    }

    /**
     * Send customer an order receipt email.
     * Precondition: The order payment has been successful
     */
    public function sendReceipt()
    {
        $subject = _t(
            'SilverShop\ShopEmail.ReceiptSubject',
            'Order #{OrderNo} receipt',
            '',
            array('OrderNo' => $this->order->Reference)
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
    public function sendCancelNotification()
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
            return $email->send();
        }
    }

    /**
     * Send an email to the customer containing the latest note of {@link OrderStatusLog} and the current status.
     *
     * @param string $title Subject for email
     * @param string $note  Optional note-content (instead of using the OrderStatusLog)
     *
     * @return bool|string
     */
    public function sendStatusChange($title, $note = null)
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

        /**
         * @var Email $e
         */
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

        if ($this->debugMode) {
            $result = $this->debug($email);
        } else {
            $result = $email->send();
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
     *
     * @param Email $email
     * @return string
     */
    protected function debug(Email $email)
    {
        $email->render();
        $template = $email->getHTMLTemplate();
        $headers = $email->getSwiftMessage()->getHeaders()->toString();

        return "<h2>Email HTML template: $template</h2>\n" .
            "<pre>$headers</pre>" .
            $email->getBody();
    }
}

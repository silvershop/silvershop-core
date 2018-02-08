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
        $completemessage = $checkoutpage ? $checkoutpage->PurchaseComplete : '';

        /**
 * @var Email $email
*/
        $email = Injector::inst()->create('ShopEmail');
        $email->setHTMLTemplate($template);
        $email->setFrom($from);
        $email->setTo($to);
        $email->setSubject($subject);

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
     * @return bool
     */
    public function sendEmail($template, $subject, $copyToAdmin = true)
    {
        $email = $this->buildEmail($template, $subject);

        if ($copyToAdmin) {
            $email->setBcc(Email::config()->admin_email);
        }
        if ($this->debugMode) {
            return $email->debug();
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
            return $email->debug();
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
        $email = Injector::inst()->create(
            'ShopEmail',
            Email::config()->admin_email,
            Email::config()->admin_email,
            _t(
                'SilverShop\ShopEmail.CancelSubject',
                'Order #{OrderNo} cancelled by member',
                '',
                array('OrderNo' => $this->order->Reference)
            ),
            $this->order->renderWith(Order::class)
        );
        $email->send();
    }

    /**
     * Send an email to the customer containing the latest note of {@link OrderStatusLog} and the current status.
     *
     * @param string $title Subject for email
     * @param string $note  Optional note-content (instead of using the OrderStatusLog)
     */
    public function sendStatusChange($title, $note = null)
    {
        if (!$note) {
            $latestLog = OrderStatusLog::get()
                ->filter("OrderID", $this->order->ID)
                ->filter("SentToCustomer", 1)
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
        $e = Injector::inst()->create('ShopEmail');
        $e->setHTMLTemplate('SilverShop/Model/Order_StatusEmail');
        $e->setData(
            [
            'Order' => $this->order,
            'Note' => $note,
            'FromEmail' => $adminEmail
            ]
        );
        $e->setFrom($adminEmail);
        $e->setSubject(_t('SilverShop\ShopEmail.StatusChangeSubject', 'SilverShop – {Title}', ['Title' => $title]));
        $e->setTo($this->order->getLatestEmail());
        $e->send();
    }
}

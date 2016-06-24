<?php

/**
 * Handles email notifications to customers and / or admins.
 *
 * @package shop
 */
class OrderEmailNotifier
{
    /**
     * @var Order $order
     */
    protected $order;

    /**
     * @param Order $order
     *
     * @return OrderEmailNotifier
     */
    public static function create(Order $order)
    {
        return Injector::inst()->create('OrderEmailNotifier', $order);
    }

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
     * @param string $template
     * @param string $subject
     *
     * @return Email
     */
    private function buildEmail($template, $subject)
    {
        $from = ShopConfig::config()->email_from ? ShopConfig::config()->email_from : Email::config()->admin_email;
        $to = $this->order->getLatestEmail();
        $checkoutpage = CheckoutPage::get()->first();
        $completemessage = $checkoutpage ? $checkoutpage->PurchaseComplete : '';

        /** @var Email $email */
        $email = Injector::inst()->create('ShopEmail');
        $email->setTemplate($template);
        $email->setFrom($from);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->populateTemplate(
            array(
                'PurchaseCompleteMessage' => $completemessage,
                'Order'                   => $this->order,
                'BaseURL'                 => Director::absoluteBaseURL(),
            )
        );

        return $email;
    }

    /**
     * Send a mail of the order to the client (and another to the admin).
     *
     * @param $template    - the class name of the email you wish to send
     * @param $subject     - subject of the email
     * @param $copyToAdmin - true by default, whether it should send a copy to the admin
     *
     * @return bool
     */
    public function sendEmail($template, $subject, $copyToAdmin = true)
    {
        $email = $this->buildEmail($template, $subject);

        if ($copyToAdmin) {
            $email->setBcc(Email::config()->admin_email);
        }

        return $email->send();
    }

    /**
     * Send customer a confirmation that the order has been received
     */
    public function sendConfirmation()
    {
        $subject = _t(
            'ShopEmail.ConfirmationSubject',
            'Order #{OrderNo} confirmation',
            '',
            array('OrderNo' => $this->order->Reference)
        );
        $this->sendEmail(
            'Order_ConfirmationEmail',
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
            'ShopEmail.AdminNotificationSubject',
            'Order #{OrderNo} notification',
            '',
            array('OrderNo' => $this->order->Reference)
        );

        $this->buildEmail('Order_AdminNotificationEmail', $subject)
            ->setTo(Email::config()->admin_email)
            ->send();
    }

    /**
     * Send customer an order receipt email.
     * Precondition: The order payment has been successful
     */
    public function sendReceipt()
    {
        $subject = _t(
            'ShopEmail.ReceiptSubject',
            'Order #{OrderNo} receipt',
            '',
            array('OrderNo' => $this->order->Reference)
        );

        $this->sendEmail(
            'Order_ReceiptEmail',
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
                'ShopEmail.CancelSubject',
                'Order #{OrderNo} cancelled by member',
                '',
                array('OrderNo' => $this->order->Reference)
            ),
            $this->order->renderWith('Order')
        );
        $email->send();
    }

    /**
     * Send a message to the client containing the latest
     * note of {@link OrderStatusLog} and the current status.
     *
     * Used in {@link OrderReport}.
     *
     * @param string $note Optional note-content (instead of using the OrderStatusLog)
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
        $member = $this->order->Member();
        if (Config::inst()->get('OrderProcessor', 'receipt_email')) {
            $adminEmail = Config::inst()->get('OrderProcessor', 'receipt_email');
        } else {
            $adminEmail = Email::config()->admin_email;
        }
        $e = Injector::inst()->create('ShopEmail');
        $e->setTemplate('Order_StatusEmail');
        $e->populateTemplate(
            array(
                "Order"  => $this->order,
                "Member" => $member,
                "Note"   => $note,
            )
        );
        $e->setFrom($adminEmail);
        $e->setSubject($title);
        $e->setTo($member->Email);
        $e->send();
    }

    public static function config()
    {
        return new Config_ForClass("OrderEmailNotifier");
    }
}

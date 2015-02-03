<?php

/**
 * Handles email notifications to customers and / or admins.
 *
 * @package shop
 */
class OrderEmailNotifier{

	protected $order;

	public static function create(Order $order) {
		return Injector::inst()->create('OrderEmailNotifier', $order);
	}

	/**
	 * Assign the order to a local variable
	 * @param Order $order
	 */
	public function __construct(Order $order) {
		$this->order = $order;
	}

	
	/**
	* Send a mail of the order to the client (and another to the admin).
	*
	* @param $template - the class name of the email you wish to send
	* @param $subject - subject of the email
	* @param $copyToAdmin - true by default, whether it should send a copy to the admin
	*/
	public function sendEmail($template, $subject, $copyToAdmin = true) {
		$from = ShopConfig::config()->email_from ? ShopConfig::config()->email_from : Email::config()->admin_email;
		$to = $this->order->getLatestEmail();
		$checkoutpage = CheckoutPage::get()->first();
		$completemessage = $checkoutpage ? $checkoutpage->PurchaseComplete : "";
		$email = new Email();
		$email->setTemplate($template);
		$email->setFrom($from);
		$email->setTo($to);
		$email->setSubject($subject);
		if($copyToAdmin){
			$email->setBcc(Email::config()->admin_email);
		}
		$email->populateTemplate(array(
			'PurchaseCompleteMessage' => $completemessage,
			'Order' => $this->order,
			'BaseURL' => Director::absoluteBaseURL()
		));

		return $email->send();
	}

	/**
	 * Send customer a confirmation that the order has been received
	 */
	public function sendConfirmation() {
		$subject = sprintf(
			_t("OrderNotifier.CONFIRMATIONSUBJECT", "Order #%d Confirmation"),
			$this->order->Reference
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
	public function sendAdminNotification() {
		$subject = sprintf(
			_t("OrderNotifier.ADMINNOTIFICATIONSUBJECT", "Order #%d Notification"),
			$this->order->Reference
		);
		$this->sendEmail(
			'Order_AdminNotificationEmail', $subject, false
		);
	}

	/**
	* Send customer an order receipt email.
	* Precondition: The order payment has been successful
	*/
	public function sendReceipt() {
		$subject = sprintf(
			_t("OrderNotifier.RECEIPTSUBJECT", "Order #%d Receipt"),
			$this->order->Reference
		);
		$this->sendEmail(
			'Order_ReceiptEmail',
			$subject,
			self::config()->bcc_receipt_to_admin
		);
		$this->order->ReceiptSent = SS_Datetime::now()->Rfc2822();
		$this->order->write();
	}

	/**
	* Send a message to the client containing the latest
	* note of {@link OrderStatusLog} and the current status.
	*
	* Used in {@link OrderReport}.
	*
	* @param string $note Optional note-content (instead of using the OrderStatusLog)
	*/
	public function sendStatusChange($title, $note = null) {
		if(!$note) {
			$latestLog = OrderStatusLog::get()
				->filter("OrderID", $this->order->ID)
				->filter("SentToCustomer", 1)
				->first();
			
			if($latestLog) {
				$note = $latestLog->Note;
				$title = $latestLog->Title;
			}
		}
		$member = $this->order->Member();
		if(Config::inst()->get('OrderProcessor', 'receipt_email')) {
			$adminEmail = Config::inst()->get('OrderProcessor', 'receipt_email');
		} else {
			$adminEmail = Email::config()->admin_email;
		}
		$e = new Order_statusEmail();
		$e->populateTemplate(array(
			"Order" => $this->order,
			"Member" => $member,
			"Note" => $note
		));
		$e->setFrom($adminEmail);
		$e->setSubject($title);
		$e->setTo($member->Email);
		$e->send();
	}

	public static function config() {
		return new Config_ForClass("OrderEmailNotifier");
	}

}

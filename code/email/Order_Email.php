<?php


/**
 * @Description: Email spefically for communicating with customer about order.
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: email
 *
 **/

class Order_Email extends Email {

	static function get_from_email() {$sc = DataObject::get_one("SiteConfig"); if($sc && $sc->ReceiptEmail) {return $sc->ReceiptEmail;} else {return Email::getAdminEmail();} }

	static function get_subject() {$sc = DataObject::get_one("SiteConfig"); if($sc && $sc->ReceiptSubject) {return $sc->ReceiptSubject;} else {return "Shop Sale Information {OrderNumber}"; } }


	protected static $send_all_emails_plain = false;
		function set_send_all_emails_plain(boolean $b) {self::$send_all_emails_plain = $b;}
		function get_send_all_emails_plain() {return self::$send_all_emails_plain;}

	protected static $css_file_location = "ecommerce/css/OrderReport.css";
		function set_css_file_location($s) {self::$css_file_location = $s;}
		function get_css_file_location() {return self::$css_file_location;}

	protected static $copy_to_admin_for_all_emails = true;
		function set_copy_to_admin_for_all_emails(boolean $b) {self::$copy_to_admin_for_all_emails = $b;}
		function get_copy_to_admin_for_all_emails() {return self::$copy_to_admin_for_all_emails;}

	public function send($messageID = null, $order, $resend = false) {
		if(!$this->hasBeenSent($order) || $resend) {
			if(self::get_copy_to_admin_for_all_emails()) {
				$this->setBcc(Email::getAdminEmail());
			}
			if(self::get_send_all_emails_plain()) {
				$result = parent::sendPlain($messageID);
			}
			else {
				$result = parent::send($messageID);
			}
			$this->createRecord($result, $order);
			return $result;
		}
	}


	/**
	 *@return DataObject (OrderEmailRecord)
	 **/
	protected function createRecord($result, $order) {
		$obj = new OrderEmailRecord();
		$obj->From = $this->from;
		$obj->To = $this->to;
		$obj->Subject = $this->subject;
		$obj->Content = $this->body;
		$obj->Result = $result ? 1 : 0;
		$obj->OrderID = $order->ID;
		$obj->OrderStepID = $order->StatusID;
		if(Email::$send_all_emails_to) {
			$obj->To .= Email::$send_all_emails_to;
		}
		$obj->write();
		return $obj;
	}

	/**
	 *@return boolean
	 **/
	function hasBeenSent($order) {
		if(DataObject::get_one("OrderEmailRecord", "\"OrderEmailRecord\".\"OrderID\" = ".$order->ID." AND \"OrderEmailRecord\".\"OrderStepID\" = ".intval($order->StatusID)." AND  \"OrderEmailRecord\".\"Result\" = 1")) {
			return true;
		}
		return false;
	}

	/**
	 * moves CSS to inline CSS in email
	 *@author Mark Guinn
	 */
	protected function parseVariables($isPlain = false) {
		require_once(Director::baseFolder() . '/ecommerce/thirdparty/Emogrifier.php');
		parent::parseVariables($isPlain);
		// if it's an html email, filter it through emogrifier
		$cssFileLocation = Director::baseFolder()."/".self::get_css_file_location();
		$cssFileHandler = fopen($cssFileLocation, 'r');
		$css = fread($cssFileHandler,  filesize($cssFileLocation));
		fclose($cssFileHandler);
		$emog = new Emogrifier($this->body, $css);
		$this->body = $emog->emogrify();
	}


}

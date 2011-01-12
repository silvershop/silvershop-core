<?php


/**
 * @Description: Email spefically for communicating with customer about order.
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class Order_Email extends Email {

	public function send($messageID = null) {
		$result = parent::send($messageID);
		$this->CreateRecord($result);
		return $result;
	}

	public function sendPlain($messageID = null) {
		$result = parent::sendPlain($messageID);
		$this->CreateRecord($result);
		return $result;

	}

	protected function CreateRecord($result) {
		$obj = new OrderEmailRecord();
		$obj->From = $this->from;
		$obj->To = $this->to;
		$obj->Subject = $this->subject;
		$obj->Content = $this->body;
		$obj->Result = $result;
		if(Email::$send_all_emails_to) {
			$obj->To .= Email::$send_all_emails_to;
		}
		$obj->write();
	}

}

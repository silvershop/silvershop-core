<?php

/**
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: control
 * @Description: this class
 *
 **/

class CartResponse extends EcommerceResponse {


	/**
	 * Builds json object to be returned via ajax.
	 *
	 *@return JSON
	 **/
	public function ReturnCartData($messages = array, $data = null, $status = "success") {
		//add header
		$this->addHeader('Content-Type', 'application/json');
		if($status != "success") {
			$this->setStatusCode(400, "not successfull: ".$status." --- ".$message);
		}

		//init Order - IMPORTANT
		$currentOrder = ShoppingCart::current_order();
		$currentOrder->calculateModifiers(true);

		// populate Javascript
		$js = array ();
		if ($items = $currentOrder->Items()) {
			foreach ($items as $item) {
				$item->updateForAjax($js);
			}
		}
		if ($modifiers = $currentOrder->Modifiers()) {
			foreach ($modifiers as $modifier) {
				$modifier->updateForAjax($js);
			}
		}
		$currentOrder->updateForAjax($js);
		if($messages) {
			$messagesImploded = '';
			foreach($messages as $messageArray) {
				$messagesImploded .= '<span class="'.$messageArray["Type"].'">'.$messageArray["Message"].'</span>';
			}
			$js[] = array(
				"id" => $currentOrder->TableMessageID(),
				"parameter" => "innerHTML",
				"value" => $messagesImploded;
				"isOrderMessage" => true
			);
			$js[] = array(
				"id" =>  $currentOrder->TableMessageID(),
				"parameter" => "hide",
				"value" => 0
			);
		}
		else {
			$js[] = array(
				"id" => $currentOrder->TableMessageID(),
				"parameter" => "hide",
				"value" => 1
			);
		}


		//merge and return
		if(is_array($data)) {
			$js = array_merge($js, $data);
		}
		return Convert::array2json($js);
	}

}

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
	public function ReturnCartData($status, $message = "", $data = null) {
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
		ShoppingCart::update_for_ajax($js, $message, $status);

		//merge and return
		if(is_array($data)) {
			$js = array_merge($js, $data);
		}
		return Convert::array2json($js);
	}

}

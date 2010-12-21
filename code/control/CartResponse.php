<?php

class CartResponse extends EcommerceResponse {


	/**
	 * Builds json object to be returned via ajax.
	 */
	public function ReturnCartData($status, $message = "", $data = null) {
		$this->addHeader('Content-Type', 'application/json');
		if($status != "success") {
			$this->setStatusCode(400, "not successfull: ".$status." --- ".$message);
			return "";
		}
		else {
			$currentOrder = ShoppingCart::current_order();
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
			return Convert::array2json($js);
		}
	}

}

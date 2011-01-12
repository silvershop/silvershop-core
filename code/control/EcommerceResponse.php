<?php

/**
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 **/
abstract class EcommerceResponse extends SS_HTTPResponse {

	public function ReturnCartData($status, $message = "", $data = null) {
		return "Extend the EcommerceResponse class";
	}

}

<?php

/**
 * @authors: Jeremy, Nicolaas
 *
 **/
abstract class EcommerceResponse extends SS_HTTPResponse {

	public function ReturnCartData($status, $message = "", $data = null) {
		user_error("Make sure to extend the EcommerceResponse class for your own purposes";
	}

}

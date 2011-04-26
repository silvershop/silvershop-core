<?php

/**
 * @description: This class is a base class for Ecommerce Responses such as Cart Response
 *
 * @authors: Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: control
 *
 **/
abstract class EcommerceResponse extends SS_HTTPResponse {

	public function ReturnCartData($status, $message = "", $data = null) {
		user_error("Make sure to extend the EcommerceResponse class for your own purposes", E_USER_NOTICE);
	}

}

<?php

define('SHOP_DIR',basename(__DIR__));
define('SHOP_PATH',BASE_PATH.DIRECTORY_SEPARATOR.SHOP_DIR);

if(!class_exists('Payment'))
	user_error("You need to also install the Payment module to use the shop module", E_USER_ERROR);

//custom classes
Object::useCustomClass('Currency','ShopCurrency', true);
<?php

define('SHOP_DIR',basename(__DIR__));
define('SHOP_PATH',BASE_PATH.DIRECTORY_SEPARATOR.SHOP_DIR);

Object::useCustomClass('Currency','ShopCurrency', true);

if($checkoutsteps = CheckoutPage::config()->steps){
	SteppedCheckout::setupSteps($checkoutsteps);
}

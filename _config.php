<?php

define('SHOP_DIR',basename(__DIR__));
define('SHOP_PATH',BASE_PATH.DIRECTORY_SEPARATOR.SHOP_DIR);

if($checkoutsteps = CheckoutPage::config()->steps){
	SteppedCheckout::setupSteps($checkoutsteps);
}

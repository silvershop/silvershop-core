<?php

use SilverShop\Core\Checkout\CheckoutPage;
use SilverShop\Core\Checkout\Step\SteppedCheckout;

if($checkoutsteps = CheckoutPage::config()->steps){
	SteppedCheckout::setupSteps($checkoutsteps);
}

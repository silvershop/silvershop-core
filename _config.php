<?php

use SilverShop\Page\CheckoutPage;
use SilverShop\Checkout\Step\SteppedCheckout;

if($checkoutsteps = CheckoutPage::config()->steps){
	SteppedCheckout::setupSteps($checkoutsteps);
}

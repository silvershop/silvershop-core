<?php

use SilverShop\Extension\SteppedCheckoutExtension;
use SilverShop\Page\CheckoutPage;

if($checkoutsteps = CheckoutPage::config()->steps){
    SteppedCheckoutExtension::setupSteps($checkoutsteps);
}

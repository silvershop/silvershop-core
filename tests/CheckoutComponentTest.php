<?php

class CheckoutComponentTest extends SapphireTest {
	
	function testSinglePageConfig() {
		$config = new SinglePageCheckoutComponentConfig();
		$form = $config->combineFields();
	}

}
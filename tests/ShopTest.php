<?php
/**
 * Helper class for setting up shop tests
 *
 * @package shop
 * @subpackage tests
 */
class ShopTest{

	public static function setConfiguration() {
		$ds = DIRECTORY_SEPARATOR;
		include BASE_PATH.$ds.SHOP_DIR.$ds.'tests'.$ds.'test_config.php';
	}

	/**
	 * Helper function for publishing products,
	 * since the fixture system doesn't do it for us.
	 *
	 * Note: the shop.yml fixture MUST be included in the test class fixtures list
	 */
	public static function publishProducts(SapphireTest $test){
		$test->objFromFixture("Product", "socks")
			->publish("Stage", "Live");
		$test->objFromFixture("Product", "tshirt")
			->publish("Stage", "Live");
		$test->objFromFixture("Product", "mp3player")
			->publish("Stage", "Live");
	}

}

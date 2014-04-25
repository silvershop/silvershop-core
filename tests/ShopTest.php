<?php
/**
 * High level tests of the whole ecommerce module.
 *
 * @package shop
 * @subpackage tests
 */
class ShopTest extends SapphireTest {

	public function testExampleConfig() {
		$this->markTestIncomplete('get example from yaml');
	}

	public static function setConfiguration() {
		$ds = DIRECTORY_SEPARATOR;
		include BASE_PATH.$ds.SHOP_DIR.$ds.'tests'.$ds.'test_config.php';
	}

}

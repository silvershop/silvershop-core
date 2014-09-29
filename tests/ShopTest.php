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

}

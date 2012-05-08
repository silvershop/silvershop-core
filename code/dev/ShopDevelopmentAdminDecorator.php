<?php
/**
 * An extension for DevelopmentAdmin, to provide the sub-url /dev/shop
 * @package shop
 * @subpackage dev
 */
class ShopDevelopmentAdminDecorator extends Extension{

	function shop($request) {
		if(Director::is_cli()) {
			$da = Object::create('ShopDatabaseAdmin');
			return $da->handleRequest($request);
		} else {
			$renderer = Object::create('DebugView');
			$renderer->writeHeader();
			$renderer->writeInfo(_t("Shop.DEVTOOLSTITLE","Shop Development Tools"), Director::absoluteBaseURL());
			echo "<div style=\"margin: 0 2em\">";

			$da = Object::create('ShopDatabaseAdmin');
			return $da->handleRequest($request);

			echo "</div>";
			$renderer->writeFooter();

		}
	}

}
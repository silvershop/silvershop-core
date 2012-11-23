<?php

/**
 * Product Catalog Admin
 * @package shop
 * @subpackage cms
 **/
class ProductCatalogAdmin extends ModelAdmin {

	static $menu_priority = 2;
	
	public static $managed_models = array("Product", "ProductCategory","ProductVariation","ProductAttributeType");

	public static function set_managed_models(array $array) {
		self::$managed_models = $array;
	}

	public static $url_segment = 'products';
	public static $menu_title = 'Products';

}
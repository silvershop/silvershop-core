<?php

/**
 * Product Catalog Admin
 * @package shop
 * @subpackage cms
 **/
class ProductCatalogAdmin extends ModelAdmin {

	private static $url_segment = 'catalog';
	private static $menu_title = 'Catalog';
	private static $menu_priority = 5;
	private static $menu_icon = 'shop/images/icons/catalog-admin.png';
	private static $managed_models = array(
		"Product","ProductCategory","ProductAttributeType"
	);
	private static $model_importers = array(
		"Product" => "ProductBulkLoader"
	);

}

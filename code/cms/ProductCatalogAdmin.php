<?php

/**
 * Product Catalog Admin
 * @package shop
 * @subpackage cms
 **/
class ProductCatalogAdmin extends ModelAdmin {

	private static $url_segment = 'products';
	private static $menu_title = 'Products';
	private static $menu_priority = 2;
	private static $managed_models = array("Product", "ProductCategory","ProductVariation","ProductAttributeType");
	
	private static $model_importers = array(
		"Product" => "ProductBulkLoader"	
	);

}
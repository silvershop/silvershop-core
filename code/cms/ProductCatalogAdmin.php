<?php

/**
 * Product Catalog Admin
 * @package shop
 * @subpackage cms
 **/
class ProductCatalogAdmin extends ModelAdmin {

	static $menu_priority = 2;
	
	private static $managed_models = array("Product", "ProductCategory","ProductVariation","ProductAttributeType");

	public static $url_segment = 'products';
	public static $menu_title = 'Products';
	
	private static $model_importers = array(
		"Product" => "ProductBulkLoader"	
	);

}
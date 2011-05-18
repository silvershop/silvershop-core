<?php

/**
 * @description: CMS management for everything you have sold and all related data (e.g. logs, payments)
 *
 * @authors: Silverstripe, Jeremy, Romain, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 **/

class SalesAdmin extends ModelAdmin{

	public static $url_segment = 'sales';

	public static $menu_title = 'Sales';

	public static $menu_priority = 1;

	//static $url_priority = 50;

	public static $managed_models = array('Order','OrderStatusLog', 'OrderItem', 'OrderModifier', 'OrderEmailRecord', 'BillingAddress', 'ShippingAddress','Payment');
		public static function set_managed_models(array $a) {self::$managed_models = $a;}
		public static function add_managed_model($s) {self::$managed_models[] = $s;}
		public static function remove_managed_model($s) {
			if(self::$managed_models && count(self::$managed_models)){
				foreach(self::$managed_models as $key => $model) {
					if($model == $s) {
						unset(self::$managed_models[$key]);
					}
				}
			}
		}

	public static $collection_controller_class = 'SalesAdmin_CollectionController';

	public static $record_controller_class = 'SalesAdmin_RecordController';


	function init() {
		parent::init();
		Requirements::themedCSS("OrderReport");
		Requirements::javascript("ecommerce/javascript/EcomModelAdminExtensions.js");
	}


	function urlSegmenter() {
		return self::$url_segment;
	}
}

class SalesAdmin_CollectionController extends ModelAdminEcommerceClass_CollectionController {


}

//remove delete action
class SalesAdmin_RecordController extends ModelAdminEcommerceClass_RecordController {




}

<?php

/**
 *
 * @authors: Silverstripe, Jeremy, Romain, Nicolaas
 * @description: manages everything you have sold and all related data (e.g. logs, payments)
 **/

class StoreAdmin extends ModelAdmin{

	public static $url_segment = 'shop';

	public static $menu_title = 'Shop';

	public static $menu_priority = 1;

	//static $url_priority = 50;

	public static $managed_models = array('OrderStep', 'EcommerceCountry');
		public static function set_managed_models(array $array) {self::$managed_models = $array;}
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

	public static $collection_controller_class = 'StoreAdmin_CollectionController';

	public static $record_controller_class = 'StoreAdmin_RecordController';

	function init() {
		parent::init();
	}


	/**
	 *
	 *@return String (URLSegment)
	 **/
	function urlSegmenter() {
		return self::$url_segment;
	}
}

class StoreAdmin_CollectionController extends ModelAdminEcommerceClass_CollectionController {


}

//remove delete action
class StoreAdmin_RecordController extends ModelAdminEcommerceClass_RecordController {




}

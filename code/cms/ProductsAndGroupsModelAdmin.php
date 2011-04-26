<?php

/**
 * @description: Manages everything you sell.
 * Products and Product Groups are included by default - can also include ProductVariations, etc..
 *
 * @authors: Silverstripe, Jeremy, Tony, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: cms
 *
 **/

class ProductsAndGroupsModelAdmin extends ModelAdmin {

	public static $menu_priority = 2;

	public static $collection_controller_class = 'ProductsAndGroupsModelAdmin_CollectionController';

	public static $record_controller_class = 'ProductsAndGroupsModelAdmin_RecordController';

	public static $managed_models = array('Product', 'ProductGroup');
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

	public static $url_segment = 'products';

	public static $menu_title = 'Products';

	public static $model_importers = array(
		'Product' => 'ProductBulkLoader',
		'ProductGroup' => null
	);

	function init() {
		parent::init();
		Requirements::javascript("ecommerce/javascript/EcomModelAdminExtensions.js");
	}

	/**
	 *@return String (URL Segment)
	 **/
	function urlSegmenter() {
		return self::$url_segment;
	}

}
//remove side forms
class ProductsAndGroupsModelAdmin_CollectionController extends ModelAdminEcommerceClass_CollectionController {

	//public function CreateForm() {return false;}
	//public function ImportForm() {return false;}

	 //note that these are called once for each $managed_models

	/**
	 *
	 *@return Form
	 **/
	function ImportForm(){
		$form = parent::ImportForm();
		if($form){
			//EmptyBeforeImport checkbox does not appear to work for SiteTree objects, so removed for now
			$form->Fields()->removeByName('EmptyBeforeImport');
		}
		return $form;
	}

	/*
	//see issue 145
	function ResultsForm($searchCriteria){
		$form = parent::ResultsForm($searchCriteria);
		if($tf = $form->Fields()->fieldByName($this->modelClass)){
			$tf->actions['create'] = array(
				'label' => 'delete',
				'icon' => null,
				'icon_disabled' => 'cms/images/test.gif',
				'class' => 'testlink'
			);

			$tf->setPermissions(array(
				'create'
			));
		}
		return $form;
	}
	*/

}

class ProductsAndGroupsModelAdmin_RecordController extends ModelAdminEcommerceClass_RecordController{


}

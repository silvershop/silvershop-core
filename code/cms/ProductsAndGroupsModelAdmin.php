<?php

/**
 * @authors: Silverstripe, Jeremy, Tony, Nicolaas
 * @description: Manages everything you sell including modifiers
 **/

class ProductsAndGroupsModelAdmin extends ModelAdmin {

	public static $menu_priority = 2;

	public static $collection_controller_class = 'ProductsAndGroupsModelAdmin_CollectionController';

	public static $record_controller_class = 'ProductsAndGroupsModelAdmin_RecordController';

	public static $managed_models = array('Product', 'ProductGroup');
		public static function set_managed_models(array $array) {self::$managed_models = $array;}
		public static function add_managed_model($item) {self::$managed_models[] = $item;}
		public static function remove_managed_model($item) {
			if(self::$managed_models && count(self::$managed_models)){
				foreach(self::$managed_models as $key => $model) {
					if($model == $item) {
						unset(self::$managed_models[$key]);
					}
				}
			}
		}

	public static $url_segment = 'products';

	public static $menu_title = 'Products';

	public static $model_importers = array(
		'Product' => 'ProductBulkLoader',
		'ProductGroup' => null,
		'ProductVariation' => null
	);

	function init() {
		parent::init();
		Requirements::javascript("ecommerce/javascript/EcommerceModelAdminExtensions.js");
	}

	function urlSegmenter() {
		return self::$url_segment;
	}

}
//remove side forms
class ProductsAndGroupsModelAdmin_CollectionController extends ModelAdminEcommerceClass_CollectionController {

	//public function CreateForm() {return false;}
	//public function ImportForm() {return false;}

	 //note that these are called once for each $managed_models

	function ImportForm(){
		$form = parent::ImportForm();
		if($form){
			//EmptyBeforeImport checkbox does not appear to work for SiteTree objects, so removed for now
			$form->Fields()->removeByName('EmptyBeforeImport');
		}
		return $form;
	}

	/*
	//TODO: Half-started attempt at modifying the way products are deleted - they should be deleted from both stages
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

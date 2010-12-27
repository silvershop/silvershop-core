<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 **/

class ProductsAndGroupsModelAdmin extends ModelAdmin {

	public static $menu_priority = 2;

	public static $collection_controller_class = "ProductsAndGroupsModelAdmin_CollectionController";

	public static $record_controller_class = "ProductsAndGroupsModelAdmin_RecordController";

	public static $managed_models = array("Product", "ProductGroup","ProductVariation","ProductAttributeType");

	public static function set_managed_models(array $array) {
		self::$managed_models = $array;
	}

	public static $url_segment = 'products';

	public static $menu_title = 'Products';

	public static $model_importers = array(
		'Product' => 'ProductBulkLoader',
		'ProductGroup' => null,
		'ProductVariation' => null
	);

}
//remove side forms
class ProductsAndGroupsModelAdmin_CollectionController extends ModelAdmin_CollectionController {

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


	//TODO: Half-started attempt at modifying the way products are deleted - they should be deleted from both stages
	function ResultsForm($searchCriteria){
		$form = parent::ResultsForm($searchCriteria);
		if($tf = $form->Fields()->fieldByName($this->modelClass)){
			/*$tf->actions['create'] = array(
				'label' => 'delete',
				'icon' => null,
				'icon_disabled' => 'cms/images/test.gif',
				'class' => 'testlink'
			);*/

			/*$tf->setPermissions(array(
				'create'
			));*/
		}
		return $form;
	}

}

class ProductsAndGroupsModelAdmin_RecordController extends ModelAdmin_RecordController{

	protected static $actions_to_keep = array(
		"Back",
		"doDelete",
		"doSave"
	);

	/**
	 * Returns a form for editing the attached model
	 */
	public function EditForm() {
		$form = parent::EditForm();
		$oldActions = $form->Actions();
		//in order of appearance
		//$form->unsetActionByName("action_doDelete"); - USEFUL TO KEEP
		$form->unsetActionByName("action_unpublish");
		$form->unsetActionByName("action_delete");
		$form->unsetActionByName("action_save");
		$form->unsetActionByName("action_publish");
		//$form->unsetActionByName("action_doSave"); - USEFUL TO KEEP
		$actions = $form->Actions();
		$actions->push(new FormAction("doGoto", "go to page"));
		$form->setActions($actions);
		return $form;
	}

	function doSave($data, $form, $request) {
		$form->saveInto($this->currentRecord);

		if($this->currentRecord instanceof SiteTree){
			$this->currentRecord->writeToStage("Stage");
			$this->currentRecord->publish("Stage", "Live");
		}else{
			$this->currentRecord->write();
		}
		$this->currentRecord->flushCache();
		if(Director::is_ajax()) {
			return $this->edit($request);
		}
		else {
			Director::redirectBack();
		}
	}

	function doGoto($data, $form, $request) {
		Director::redirect($this->currentRecord->Link());
	}


	function doDelete() {
		user_error("this function has not been implemented yet", E_USER_NOTICE);
		//might be prudent not to allow deletions as products should not be deleted, but rather be made "not for sale"
	}

}

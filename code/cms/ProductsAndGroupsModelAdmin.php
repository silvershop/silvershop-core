<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 **/

class ProductsAndGroupsModelAdmin extends ModelAdmin {

	static $menu_priority = 2;

	public static $collection_controller_class = "ProductsAndGroupsModelAdmin_CollectionController";

	public static $record_controller_class = "ProductsAndGroupsModelAdmin_RecordController";

	public static $managed_models = array("Product", "ProductGroup");
		public static function set_managed_models(array $array) {self::$managed_models = $array;}
		public static function add_managed_models($v) {self::$managed_models[] = $v;}
		public static function remove_managed_models($v) {self::$managed_models[array_search($v, self::$managed_models)] = $v;}

	public static $url_segment = 'products';

	public static $menu_title = 'Products';

}
//remove side forms
class ProductsAndGroupsModelAdmin_CollectionController extends ModelAdmin_CollectionController {

	//public function CreateForm() {return false;}
	public function ImportForm() {return false;}
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
		$this->currentRecord->writeToStage("Stage");
		$this->currentRecord->publish("Stage", "Live");
		$this->currentRecord->flushCache();
		if(Director::is_ajax()) {
			return $this->edit($request);
		} else {
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

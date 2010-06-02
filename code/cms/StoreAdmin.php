<?php
class StoreAdmin extends ModelAdmin{

	static $url_segment = 'store';
	static $menu_title = 'Store';
	
	static $menu_priority = 1;
	
	//static $url_priority = 50;
	
	public static $managed_models = array('Order','Payment','Product');

	public static $collection_controller_class = 'StoreAdmin_CollectionController';
	public static $record_controller_class = 'StoreAdmin_RecordController';
}

//remove side forms
class StoreAdmin_CollectionController extends ModelAdmin_CollectionController {

	//public function CreateForm() {return false;}
	public function ImportForm() {return false;}
}

//remove delete action
class StoreAdmin_RecordController extends ModelAdmin_RecordController {

	public function EditForm() {
		$form = parent::EditForm();
		$form->Actions()->removeByName('Delete');
		return $form;
	}
}

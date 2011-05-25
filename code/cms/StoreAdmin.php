<?php

class StoreAdmin extends ModelAdmin{

	static $url_segment = 'orders';

	static $menu_title = 'Orders';

	static $menu_priority = 1;

	//static $url_priority = 50;

	public static $managed_models = array('Order','Payment','OrderStatusLog', 'OrderItem', 'OrderModifier');
		public static function set_managed_models(array $array) {self::$managed_models = $array;}

	public static $collection_controller_class = 'StoreAdmin_CollectionController';

	public static $record_controller_class = 'StoreAdmin_RecordController';


	function init() {
		parent::init();
		Requirements::themedCSS("OrderReport");
		Requirements::javascript("ecommerce/javascript/EcommerceModelAdminExtensions.js");
	}


}

class StoreAdmin_CollectionController extends ModelAdmin_CollectionController {

	//public function CreateForm() {return false;}
	public function ImportForm() {return false;}

	function search($request, $form) {
		// Get the results form to be rendered
		$query = $this->getSearchQuery(array_merge($form->getData(), $request));
		$resultMap = new SQLMap($query, $keyField = "ID", $titleField = "Title");
		$items = $resultMap->getItems();
		$array = array();
		if($items && $items->count()) {
			foreach($items as $item) {
				$array[] = $item->ID;
			}
		}
		Session::set("StoreAdminLatestSearch",serialize($array));
		return parent::search($request, $form);
	}

}

//remove delete action
class StoreAdmin_RecordController extends ModelAdmin_RecordController {

	public function EditForm() {
		$form = parent::EditForm();
		$form->Actions()->removeByName('Delete');
		$array = unserialize(Session::get("StoreAdminLatestSearch"));
		if(is_array($array)) {
			if(count($array) && count($array) > 1) {
				foreach($array as $key => $id) {
					if($id == $this->currentRecord->ID) {
						if(isset($array[$key + 1]) && $array[$key + 1]) {
							$nextRecordID = $array[$key + 1];
							$nextRecordURL = 'admin/'.StoreAdmin::$url_segment.'/'.$this->currentRecord->ClassName.'/'.$nextRecordID.'/edit';
							$form->Actions()->push(new FormAction("goNext", "Next Record"));
							$form->Fields()->push(new HiddenField("nextRecordURL", null, $nextRecordURL));
						}
						if(isset($array[$key - 1]) && $array[$key - 1]) {
							$prevRecordID = $array[$key - 1];
							$nextRecordURL = 'admin/'.StoreAdmin::$url_segment.'/'.$this->currentRecord->ClassName.'/'.$prevRecordID.'/edit';
							$form->Actions()->insertFirst(new FormAction("goPrev", "Previous Record"));
							$form->Fields()->push(new HiddenField("prevRecordURL", null, $nextRecordURL));
						}
					}
				}
			}
		}
		return $form;
	}




}

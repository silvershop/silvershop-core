<?php
/**
 * Order administration interface, based on ModelAdmin
 * @package shop
 * @subpackage cms
 */
class OrdersAdmin extends ModelAdmin{

	static $url_segment = 'orders';
	static $menu_title = 'Orders';
	static $menu_priority = 1;

	public static $managed_models = array(
		'Order' => array(
			'title' => 'Orders',
			'collection_controller_class' => 'OrdersAdmin_RecordController',
			'record_controller' => 'OrdersAdmin_RecordController'
		),
		'Payment' => array('title' => 'Payments'),
	);
	public static function set_managed_models(array $array) {self::$managed_models = $array;}
	//public static $collection_controller_class = 'OrdersAdmin_CollectionController';
	//public static $record_controller_class = 'OrdersAdmin_RecordController';

	function init() {
		parent::init();
		Requirements::javascript(SHOP_DIR."/javascript/EcommerceModelAdminExtensions.js");
	}

}

/**
 * @package shop
 * @subpackage cms
 */
class OrdersAdmin_CollectionController extends ModelAdmin_CollectionController {

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

/**
 * Removes delete action
 * @package shop
 * @subpackage cms
 */
class OrdersAdmin_RecordController extends ModelAdmin_RecordController {
	
	static $allowed_actions = array(
		'recalculate'
	);
	
	public function EditForm() {
		$form = parent::EditForm();
		//remove delete action
		$form->Actions()->removeByName('Delete');
		//add next / previous actions
		$array = unserialize(Session::get("StoreAdminLatestSearch"));
		if(is_array($array) && count($array) && count($array) > 1) {
			foreach($array as $key => $id) {
				if($id == $this->currentRecord->ID) {
					if(isset($array[$key + 1]) && $array[$key + 1]) {
						$nextRecordID = $array[$key + 1];
						$nextRecordURL = $this->parentController->Link().'/'.$nextRecordID.'/edit';
						$form->Actions()->push(new FormAction("goNext", "Next Record"));
						$form->Fields()->push(new HiddenField("nextRecordURL", null, $nextRecordURL));
					}
					if(isset($array[$key - 1]) && $array[$key - 1]) {
						$prevRecordID = $array[$key - 1];
						$nextRecordURL = $this->parentController->Link().'/'.$prevRecordID.'/edit';
						$form->Actions()->insertFirst(new FormAction("goPrev", "Previous Record"));
						$form->Fields()->push(new HiddenField("prevRecordURL", null, $nextRecordURL));
					}
				}
			}
		}
		//add recalculate action
		$link = $this->Link('recalculate');
		$form->Fields()->addFieldToTab("Root.AdminActions",
			new LiteralField("recalculate",
				"<a href=\"$link\">recalculate order</a>"
			)
		);
		return $form;
	}
	
	public function recalculate(){
		if(!$this->currentRecord){
			return false;
		}
		$order = $this->currentRecord;
		//TODO: only recalculate if all order items have retrievable product versions
		$order->calculate();
		$order->write();
		return "success: ".$order->Total();
	}
	
}
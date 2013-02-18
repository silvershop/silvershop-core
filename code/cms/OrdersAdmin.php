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
			'collection_controller' => 'OrdersAdmin_CollectionController',
			'record_controller' => 'OrdersAdmin_RecordController'
		),
		'Payment' => array('title' => 'Payments'),
	);
	
	public static function set_managed_models(array $array) {
		self::$managed_models = $array;
	}

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

	function SearchForm(){
		$form = parent::SearchForm();
		$form->Fields()->fieldByName("Status")->setValue(null); //make status checkbox field set empty
		return $form;
	}
	
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
		'recalculate',
		'printorder'
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
		$recalculatelink = $this->Link('recalculate');
		$printlink = $this->Link('printorder')."?print=1";
		
		$printwindowjs =<<<JS
			window.open('$printlink', 'print_order', 'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50');return false;
JS;
		
		$form->Actions()->insertFirst(
			new LiteralField("PrintOrder","<input type=\"submit\" onclick=\"javascript:$printwindowjs\" class=\"action\" value=\""._t("Order.PRINT","Print")."\">")
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
		if(Director::is_ajax()){
			return "success: ".$order->Total();
		}
		Director::redirectBack();
	}
	
	public function printorder(){
		//include print javascript, if print argument is provided
		if(isset($_REQUEST['print']) && $_REQUEST['print']) {
			Requirements::customScript("if(document.location.href.indexOf('print=1') > 0) {window.print();}");
		}
		$this->Title = i18n::_t("ORDER.INVOICE","Invoice");
		if($id = $this->urlParams['ID']) {
			$this->Title .= " #$id";
		}
		Requirements::clear();
		return $this->currentRecord->customise(array(
			'SiteConfig' => SiteConfig::current_site_config(),
			'Now' => $this->Now()
		))->renderWith('Order_Printable');
	}
	
}
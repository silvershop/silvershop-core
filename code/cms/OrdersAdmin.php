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

}

/**
 * @package shop
 * @subpackage cms
 */
class OrdersAdmin_CollectionController extends ModelAdmin_CollectionController {

	public function ImportForm() {return false;}

	function SearchForm(){
		$form = parent::SearchForm();
		$form->Fields()->fieldByName("Status")->setValue(array()); //make status checkbox field set empty by default
		return $form;
	}
	
	/**
	 * Force search query to be within given statuses (as if all selected), if none are selected.
	 */
	function getSearchQuery($searchCriteria){
		$query = parent::getSearchQuery($searchCriteria);
		if(empty($searchCriteria['Status'])){
			$statuses = $this->SearchForm()->Fields()->fieldByName("Status")->getSource();
			$query->where("Status IN('".implode("','",$statuses)."')");
		}
		return $query;
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
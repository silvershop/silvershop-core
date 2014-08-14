<?php
/**
 * Order administration interface, based on ModelAdmin
 * @package shop
 * @subpackage cms
 */
class OrdersAdmin extends ModelAdmin{

	private static $url_segment = 'orders';
	private static $menu_title = 'Orders';
	private static $menu_priority = 1;
	private static $menu_icon = 'shop/images/icons/order-admin.png';

	private static $managed_models = array(
		'Order' => array(
			'title' => 'Orders'
		)
	);

	public function getList() {
		$context = $this->getSearchContext();
		$params = $this->request->requestVar('q');
		//TODO update params DateTo, to include the day, ie 23:59:59
		$list = $context->getResults($params)
			->exclude("Status",Order::config()->hidden_status); //exclude hidden statuses

		$this->extend('updateList', $list);

		return $list;
	}

	function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		if($this->modelClass == "Order"){
			$form->Fields()->fieldByName("Order")->getConfig()
				->getComponentByType('GridFieldDetailForm')
				->setItemRequestClass('OrderGridFieldDetailForm_ItemRequest'); //see below
		}

		return $form;
	}

}

class OrderGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest{

	private static $allowed_actions = array(
		'printorder'
	);
		
	public function ItemEditForm() {
		$form = parent::ItemEditForm();

		$printlink = $this->Link('printorder')."?print=1";
		$printwindowjs =<<<JS
			window.open('$printlink', 'print_order', 'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50');return false;
JS;
		$form->Actions()->push(
			new LiteralField("PrintOrder",
				"<input class=\"action no-ajax grid-print-button\" data-icon=\"grid_print\" type=\"submit\" onclick=\"javascript:$printwindowjs\" value=\""._t("Order.PRINT","Print")."\" >"
			)
		);

		return $form;
	}

	public function printorder() {
		Requirements::clear();
		//include print javascript, if print argument is provided
		if(isset($_REQUEST['print']) && $_REQUEST['print']) {
			Requirements::customScript("if(document.location.href.indexOf('print=1') > 0) {window.print();}");
		}
		$title = i18n::_t("ORDER.INVOICE","Invoice");
		if($id = $this->popupController->getRequest()->param('ID')) {
			$title .= " #$id";
		}
		
		return $this->record->customise(array(
			'SiteConfig' => SiteConfig::current_site_config(),
			'Title' => $title
		))->renderWith('Order_Printable');
	}

}
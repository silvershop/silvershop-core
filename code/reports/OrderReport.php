<?php
/**
 * This is a stand-alone controller, designed to be
 * used with the eCommerce reporting system.
 * 
 * It allows a user to view a template for a packing
 * slip of an order, or an invoice with status logs.
 * 
 * @see CurrentOrdersReport
 * @see UnprintedOrderReport
 * 
 * @package ecommerce
 */
class OrderReport_Popup extends Controller {
	
	/**
	 * This is the default action of this
	 * controller without calling any
	 * explicit action, such as "show".
	 * 
	 * This default "action" will show
	 * order information in a printable view.
	 */
	function index() {
		return $this->renderWith('OrderInformation_Print');
	}
	
	/**
	 * This action shows a packing slip
	 * for the current order we're looking at.
	 */
	function packingslip() {
		return $this->renderWith('OrderInformation_PackingSlip');
	}

	/**
	 * This action shows an invoice template
	 * for the current order we're looking at.
	 */
	function invoice() {
		return $this->renderWith('OrderInformation_Print');
	}

	function Link($action = null) {
		return "OrderReport_Popup/$action";
	}
	
	/**
	 * This method is used primarily for cheque orders.
	 * 
	 * @TODO Why is this specific to cheque?
	 *
	 * @return unknown
	 */
	function SingleOrder(){
		$id = $this->urlParams['ID'];

		if(is_numeric($id)) {
			$order = DataObject::get_by_id('Order', $id);
			$payment = $order->Payment();
			$cheque = false;
			
			if($payment->First()) {
				$record = $payment->First();
				if($record->ClassName == 'ChequePayment') {
					$cheque = true;
				}
			}
			
			return new ArrayData(array(
				'DisplayFinalisedOrder' => $order,
				'IsCheque' => $cheque
			));
		}
		
		return false;
	}
	
	/**
	 * @TODO Get orders by ID or using current filter if ID is not numeric (for getting all orders)
	 * @TODO Define what the role of this method is. Is it for templates, is it for a report?
	 *
	 * @return unknown
	 */
	function DisplayFinalisedOrder() {
		$id = $this->urlParams['ID'];

		if(is_numeric($id)) {
			$order = DataObject::get_by_id("Order", $id);
			if(isset($_REQUEST['print'])) {
				$order->updatePrinted(true);
			}
			
			return $order;
		}

		return false;
	}

	/**
	 * Return a {@link Form} allowing a user to change the status
	 * of an order using {@link OrderStatusLog} records.
	 *
	 * @TODO Tidy up JS, and switch it over to jQuery instead of prototype.
	 * 
	 * @return Form
	 */
	function StatusForm() {
		Requirements::css('cms/css/layout.css');
		Requirements::css('cms/css/cms_right.css');
		Requirements::css('ecommerce/css/OrderReport.css');

		Requirements::javascript('jsparty/loader.js');
		Requirements::javascript('jsparty/behaviour.js');
		Requirements::javascript('jsparty/prototype.js');
		Requirements::javascript('jsparty/prototype_improvements.js');

		$id = (isset($_REQUEST['ID'])) ? $_REQUEST['ID'] : $this->urlParams['ID'];
		
		if(is_numeric($id)) {
			$order = DataObject::get_by_id('Order', $id);
			$member = $order->Member();
			
			$fields = new FieldSet(
				new HeaderField(_t('OrderReport.CHANGESTATUS', 'Change Order Status'), 3),
				$order->obj('Status')->formField('Status', null, null, $order->Status),
				new TextareaField('Note', _t('OrderReport.NOTEEMAIL', 'Note/Email')),
				new CheckboxField('SentToCustomer', sprintf(_t('OrderReport.SENDNOTETO', "Send this note to %s (%s)"), $member->Title, $member->Email), true),
				new HiddenField('ID', 'ID', $order->ID)
			);

			$actions = new FieldSet(
				new FormAction('doStatusForm', 'Save Status')
			);

			$form = new Form(
				$this,
				'StatusForm',
				$fields,
				$actions
			);
			
			return $form;
		}
	}

	/**
	 * Return a form with a table in it showing
	 * all the statuses for the current Order
	 * instance that we're viewing.
	 *
	 * @TODO Rename this to StatusLogForm, and check templates.
	 * 
	 * @return Form
	 */
	function StatusLog() {
		$table = new TableListField(
			'StatusTable',
			'OrderStatusLog',
			array(
				'ID' => 'ID',
				'Created' => 'Created',
				'Status' => 'Status',
				'Note' => 'Note',
				'SentToCustomer' => 'Sent to customer',
			),
			"OrderID = {$this->urlParams['ID']}"
		);

		$table->setFieldCasting(array(
			'Created' => 'Date',
			'SentToCustomer' => 'Boolean->Nice',
		));

		$table->IsReadOnly = true;

		return new Form(
			$this,
			'OrderStatusLogForm',
			new FieldSet(
				new HeaderField('Order Status History',3),
				new HiddenField('ID'),
				$table
			),
			new FieldSet()
		);
	}

	/**
	 * Form submit handler for StatusForm()
	 *
	 * @param array $data Request data from form
	 * @param Form $form The form object submitted on
	 */
	function doStatusForm($data, $form) {
		if(!is_numeric($data['ID'])) {
			return false;
		}
		
		$order = DataObject::get_by_id("Order", $data['ID']);

		// if the status was changed or a note was added, create a new log-object
		if(!empty($data['Note']) || $data['Status'] != $order->Status) {
			$orderlog = new OrderStatusLog();
			$orderlog->OrderID = $order->ID;
			$form->saveInto($orderlog);
			$orderlog->write();
		}
		
		// save the order
		if($order) {
			$form->saveInto($order);
			$order->write();
		}
		
		if($_REQUEST['SentToCustomer']) {
			$order->sendStatusChange();
		}

		return FormResponse::respond();
	}	
	
}
?>
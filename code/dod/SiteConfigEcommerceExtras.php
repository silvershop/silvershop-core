<?php

/**
 *@description: adds a few parameters for e-commerce to the SiteConfig.
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 **/

class SiteConfigEcommerceExtras extends DataObjectDecorator {

	function extraStatics(){
		return array(
			'db' => array(
				"ShopClosed" => "Boolean",
				"ReceiptEmail" => "Varchar(255)",
				"ReceiptSubject" => "Varchar(255)",
				"DispatchEmailSubject" => "Varchar(255)",
				"PostalCodeURL" => "Varchar(255)",
				"PostalCodeLabel" => "Varchar(255)",
				"NumberOfProductsPerPage" => "Int",
				"SendInvoiceOnSubmit" => "Boolean",
				"SendReceiptOnPaid" => "Boolean"
			),
			'defaults' => array(
				"SendReceiptOnPaid" => 1
			)
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		//new section

		$shoptabs = new TabSet('Shop',
			$productstab = new Tab('Products',
				new CheckboxField("ShopClosed", "Shop closed"),
				new NumericField("NumberOfProductsPerPage", "Numer of products per page")
			),
			$productstab = new Tab('Checkout',
				new TextField("PostalCodeLink", "Postal code link"),
				new TextField("PostalCodeLabel", "Postal code label")
			),
			$checkouttab = new Tab('Emails',
				new EmailField("ReceiptEmail", "From email address for shop receipt (e.g. sales@myshop.com)"),
				new TextField("ReceiptSubject", "Subject for shop receipt email ('{OrderNumber}' will be replaced with actual order number - e.g. 'thank you for your order (#{OrderNumber})');"),
				new TextField("DispatchEmailSubject", "Default subject for dispatch email (e.g. your order has been sent)"),
				new CheckboxField("SendInvoiceOnSubmit", "Send an invoice to customer as soon as he/she submits order (before payment)"),
				new CheckboxField("SendReceiptOnPaid", "Send an receipt to customer when order has been paid in full.")
			)
			/*$processtab = new Tab('OrderProcess',
				new LiteralField('op','Include a drag-and-drop interface for customising order steps (Like WidgetArea)')
			)*/
		);
		$fields->addFieldToTab('Root',$shoptabs);
		return $fields;
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		$update = array();
		$siteConfig = DataObject::get_one("SiteConfig");
		if($siteConfig) {
			if(!$siteConfig->ReceiptEmail) {
				$siteConfig->ReceiptEmail = Email::getAdminEmail();
				$update[]= "created default entry for ReceiptEmail";
			}
			if(!$siteConfig->ReceiptSubject) {
				$siteConfig->ReceiptSubject = "Shop Sale Information {OrderNumber}";
				$update[]= "created default entry for ReceiptSubject";
			}
			if(!$siteConfig->DispatchEmailSubject) {
				$siteConfig->DispatchEmailSubject = "Your order has been dispatched";
				$update[]= "created default entry for DispatchEmailSubject";
			}
			if(!$siteConfig->NumberOfProductsPerPage) {
				$siteConfig->NumberOfProductsPerPage = 12;
				$update[]= "created default entry for NumberOfProductsPerPage";
			}
			if(count($update)) {
				$siteConfig->write();
				DB::alteration_message($siteConfig->ClassName." created/updated: ".implode(" --- ",$update), 'created');
			}
		}
	}
}

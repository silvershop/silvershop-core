<?php

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *
 *
 **/

class SiteConfigEcommerceExtras extends DataObjectDecorator {

	function extraStatics(){
		return array(
			'db' => array(
				"ShopClosed" => "Boolean",
				"ReceiptEmail" => "Varchar(255)",
				"ReceiptSubject" => "Varchar(255)",
				"PostalCodeURL" => "Varchar(255)",
				"PostalCodeLabel" => "Varchar(255)"
			)
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab("Root.Webshop", new CheckboxField("ShopClosed", "Shop closed"));
		$fields->addFieldToTab("Root.Webshop", new EmailField("ReceiptEmail", "From email address for shop receipt (e.g. sales@myshop.com)"));
		$fields->addFieldToTab("Root.Webshop", new TextField("ReceiptSubject", "Subject for shop receipt email ('{OrderNumber}' will be replaced with actual order number - e.g. 'thank you for your order (#{OrderNumber})');"));
		$fields->addFieldToTab("Root.Webshop", new TextField("PostalCodeLink", "Postal code link"));
		$fields->addFieldToTab("Root.Webshop", new TextField("PostalCodeLabel", "Postal code label"));
		return $fields;
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		$update = array();
		$siteConfig = DataObject::get_one("SiteConfig");
		if($siteConfig) {
			/*
			if(!$siteConfig->ReceiptEmail) {
				$siteConfig->ReceiptEmail = Order::get_receipt_email();
				$update[]= "created default entry for ReceiptEmail";
			}
			if(!$siteConfig->ReceiptSubject) {
				$siteConfig->ReceiptSubject = Order::get_receipt_subject();
				$update[]= "created default entry for ReceiptSubject";
			}
			if(!$siteConfig->PostalCodeURL) {
				$siteConfig->PostalCodeURL = EcommerceRole::get_postal_code_url();
				$update[]= "created default entry for PostalCodeURL";
			}
			if(!$siteConfig->PostalCodeLabel) {
				$siteConfig->PostalCodeLabel = EcommerceRole::get_postal_code_label();
				$update[]= "created default entry for PostalCodeLabel";
			}
			if(count($update)) {
				$siteConfig->write();
				DB::alteration_message($siteConfig->ClassName." created/updated: ".implode(" --- ",$update), 'created');
			}
			*/
		}
	}
}

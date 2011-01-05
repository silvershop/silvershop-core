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
				"DispatchEmailSubject" => "Varchar(255)",
				"PostalCodeURL" => "Varchar(255)",
				"PostalCodeLabel" => "Varchar(255)",
				"NumberOfProductsPerPage" => "Int"
			)
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab("Root.Webshop", new HeaderField("ProductDisplay", "Product Display", 3));
		$fields->addFieldToTab("Root.Webshop", new NumericField("NumberOfProductsPerPage", "Numer of products per page"));
		//new section
		$fields->addFieldToTab("Root.Webshop", new HeaderField("Checkout", "Checkout", 3));
		$fields->addFieldToTab("Root.Webshop", new CheckboxField("ShopClosed", "Shop closed"));
		$fields->addFieldToTab("Root.Webshop", new TextField("PostalCodeLink", "Postal code link"));
		$fields->addFieldToTab("Root.Webshop", new TextField("PostalCodeLabel", "Postal code label"));
		//new section
		$fields->addFieldToTab("Root.Webshop", new HeaderField("Emails", "Emails to Customer", 3));
		$fields->addFieldToTab("Root.Webshop", new EmailField("ReceiptEmail", "From email address for shop receipt (e.g. sales@myshop.com)"));
		$fields->addFieldToTab("Root.Webshop", new TextField("ReceiptSubject", "Subject for shop receipt email ('{OrderNumber}' will be replaced with actual order number - e.g. 'thank you for your order (#{OrderNumber})');"));
		$fields->addFieldToTab("Root.Webshop", new TextField("DispatchEmailSubject", "Default subject for dispatch email (e.g. your order has been sent)"));
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
			if(count($update)) {
				$siteConfig->write();
				DB::alteration_message($siteConfig->ClassName." created/updated: ".implode(" --- ",$update), 'created');
			}
		}
	}
}

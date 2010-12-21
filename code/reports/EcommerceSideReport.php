<?php
/**
 * EcommerceSideReport classes are to allow quick reports that can be accessed
 * on the Reports tab to the left inside the SilverStripe CMS.
 * Currently there are reports to show products flagged as 'FeatuedProduct',
 * as well as a report on all products within the system.
 *
 * @package ecommerce
 */
class EcommerceSideReport_FeaturedProducts extends SS_Report {

	function title() {
		return _t('EcommerceSideReport.FEATUREDPRODUCTS', "Featured Products");
	}

	function group() {
		return _t('EcommerceSideReport.ECOMMERCEGROUP', "ECommerce");
	}
	function sort() {
		return 0;
	}
	function records() {
		return DataObject::get("Product", "\"FeaturedProduct\" = 1", "\"Title\"");
	}

	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true
			)
		);
	}
}

class EcommerceSideReport_AllProducts extends SS_Report {

	function title() {
		return _t('EcommerceSideReport.ALLPRODUCTS', "All Products");
	}
	
	function group() {
		return _t('EcommerceSideReport.ECOMMERCEGROUP', "ECommerce");
	}
	function sort() {
		return 0;
	}
	function records() {
		return DataObject::get("Product", "", "\"Title\"");
	}

	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true
			)
		);
	}

}

class EcommerceSideReport_NoImageProducts extends SS_Report {
	
	function title() {
		return _t('EcommerceSideReport.NOIMAGE',"Products with no image");
	}
	function group() {
		return _t('EcommerceSideReport.ECOMMERCEGROUP', "ECommerce");
	}
	function sort() {
		return 0;
	}
	function sourceRecords($params = null) {
		return DataObject::get("Product", "\"Product\".\"ImageID\" IS NULL OR \"Product\".\"ImageID\" <= 0", "\"Title\" ASC");
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true
			)
		);
	}
}

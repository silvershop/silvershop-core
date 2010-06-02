<?php
/**
 * EcommerceSideReport classes are to allow quick reports that can be accessed
 * on the Reports tab to the left inside the SilverStripe CMS.
 * Currently there are reports to show products flagged as 'FeatuedProduct',
 * as well as a report on all products within the system.
 * 
 * @package ecommerce
 */
class EcommerceSideReport_FeaturedProducts extends SideReport {
	
	function title() {
		return "Featured products";
	}
	
	function records() {
		return DataObject::get("Product", "FeaturedProduct = 1", "Title");
	}
	
	function fieldsToShow() {
		return array(
			"Title" => array("NestedTitle", array("2")),
		);
	}
}

class EcommerceSideReport_AllProducts extends SideReport {

	function title() {
		return "All products";
	}
	
	function records() {
		return DataObject::get("Product", "", "Title");
	}
	
	function fieldsToShow() {
		return array(
			"Title" => array("NestedTitle", array("2")),
		);
	}
	
}
?>
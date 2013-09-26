<?php
/**
 * Shop Side Report classes are to allow quick reports that can be accessed
 * on the Reports tab to the left inside the SilverStripe CMS.
 * Currently there are reports to show products flagged as 'FeatuedProduct',
 * as well as a report on all products within the system.
 *
 * @package shop
 * @subpackage reports
 */
class ShopSideReport_FeaturedProducts extends SS_Report {

	function title() {
		return _t('ShopSideReport.FEATUREDPRODUCTS', "Featured Products");
	}

	function group() {
		return _t('ShopSideReport.ShopGROUP', "Shop");
	}

	function sort() {
		return 0;
	}

	function sourceRecords($params = null) {
		return Product::get()->filter('FeaturedProduct', 1)->sort("Title");
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

/**
 * All Products Report
 * @subpackage reports
 */
class ShopSideReport_AllProducts extends SS_Report {

	function title() {
		return _t('ShopSideReport.ALLPRODUCTS', "All Products");
	}
	
	function group() {
		return _t('ShopSideReport.ShopGROUP', "Shop");
	}
	function sort() {
		return 0;
	}

	function sourceRecords($params = null) {
		return Product::get()->sort('Title');
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

class ShopSideReport_NoImageProducts extends SS_Report {
	
	function title() {
		return _t('ShopSideReport.NOIMAGE',"Products with no image");
	}
	function group() {
		return _t('ShopSideReport.ShopGROUP', "Shop");
	}
	function sort() {
		return 0;
	}
	function sourceRecords($params = null) {
		return Product::get()->where("\"Product\".\"ImageID\" IS NULL OR \"Product\".\"ImageID\" <= 0")->sort("\"Title\" ASC");
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

class ShopSideReport_HeavyProducts extends SS_Report {

	function title() {
		return _t('ShopSideReport.HEAVY',"Heavy Products");
	}
	function group() {
		return _t('ShopSideReport.ShopGROUP', "Shop");
	}
	function sort() {
		return 0;
	}
	function sourceRecords($params = null) {
		return Product::get()->where("\"Product\".\"Weight\" > 10")->sort("\"Weight\" ASC");
	}
	function columns() {
		return array(
				"Title" => array(
						"title" => "Title",
						"link" => true
				),
				"Weight" => array(
					'title' => 'Weight'	
				)
		);
	}
}

<?php
 /**
  * Product Group is a 'holder' for Products within the CMS
  * It contains functions for versioning child products
  *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: Products
 *
 **/

class ProductGroup extends Page {

	public static $db = array(
		'ChildGroupsPermission' => "Enum('Show Only Featured Products,Show All Products')",
		"NumberOfProductsPerPage" => "Int",
		"ProductsAlsoInOthersGroups" => "Boolean"
	);

	public static $has_one = array();

	public static $has_many = array();

	public static $many_many = array();

	public static $belongs_many_many = array(
		'Products' => 'Product'
	);

	public static $defaults = array();

	public static $casting = array();

	public static $default_child = 'Product';

	public static $icon = 'ecommerce/images/icons/productgroup';

	protected static $include_child_groups = true;
		static function set_include_child_groups($b = true){self::$include_child_groups = $b;}
		static function get_include_child_groups(){return self::$include_child_groups;}

	protected static $must_have_price = true;
		static function set_must_have_price($b = true){user_error("ProductGroup::\$must_have_price has been depreciated, use ProductGroup::\$only_show_products_that_can_purchase", E_USER_NOTICE);}
		static function get_must_have_price(){user_error("ProductGroup::\$must_have_price has been depreciated, use ProductGroup::\$only_show_products_that_can_purchase", E_USER_NOTICE);}

	protected static $only_show_products_that_can_purchase = false;
		static function set_only_show_products_that_can_purchase($b = true){self::$only_show_products_that_can_purchase = $b;}
		static function get_only_show_products_that_can_purchase(){return self::$only_show_products_that_can_purchase;}

	protected static $sort_options = array(
			'title' => array("Title" => 'Alphabetical', "SQL" => "\"Title\" ASC"),
			'price' => array("Title" => 'Lowest Price', "SQL" => "\"Price\" ASC"),
			'numbersold' => array("Title" => 'Most Popular', "SQL" => "\"NumberSold\" DESC")
			//'Featured' => 'Featured',
		);
		static function add_sort_option($key, $title, $sql){self::$sort_options[$key] = array("Title" => $title, "SQL" => $sql);}
		static function remove_sort_option($key){unset(self::$sort_options[$key]);}
		static function set_sort_options(array $a){self::$sort_options = $a;}
		static function get_sort_options(){return self::$sort_options;}
		protected function getSortOptionSQL($key){ // NOT STATIC
			if(isset(self::$sort_options[$key])) {
				return self::$sort_options[$key]["SQL"];
			}
			else {
				return self::$sort_options[self::get_sort_options_default()]["SQL"];
			}
		}

	protected static $sort_options_default = "title";
		static function set_sort_options_default($s){self::$sort_options_default = $s; if(!isset(self::$sort_options[$s])) {user_error("ProductGroup::set_sort_options_default got the parameter $s , however, this is not an existing sort_options key;", E_USER_NOTICE);}}
		static function get_sort_options_default(){return self::$sort_options_default;}

	protected static $featured_products_permissions = array(
		'Show Only Featured Products',
		'Show All Products'
	);

	protected static $non_featured_products_permissions = array(
		'Show All Products'
	);


	function getCMSFields() {
		$fields = parent::getCMSFields();
		if(self::$include_child_groups === 'custom'){
			$fields->addFieldToTab(
				'Root.Content',
				new Tab(
					'Products',
					new NumericField("NumberOfProductsPerPage", _t("ProductGroup.NUMBEROFPRODUCTS", "Number of products per page")),
					new HeaderField("whatproductsshown", _t("ProductGroup.WHATPRODUCTSSHOWN", 'How should products be presented in the child groups?')),
					new DropdownField(
	  					'ChildGroupsPermission',
	  					'Permission',
	  					$this->dbObject('ChildGroupsPermission')->enumValues(),
	  					'',
	  					null,
	  					_t("ProductGroup.DONOTSHOWPRODUCTS", 'Don\'t Show Any Products')
					)
				)
			);
		}
		$fields->addFieldToTab("Root.Content.Products", new CheckboxField("ProductsAlsoInOthersGroups", _t("ProductGroup.PRODUCTSALSOINOTHERSGROUPS", "Also allow the products for this product group to show in other groups (see product pages for actual selection).")));
		return $fields;
	}


	/**
	 * Retrieve a set of products, based on the given parameters. Checks get query for sorting and pagination.
	 *
	 * @param string $extraFilter Additional SQL filters to apply to the Product retrieval
	 * @param array $permissions
	 * @return DataObjectSet | Null
	 */
	function ProductsShowable($extraFilter = '', $recursive = true){
		$filter = ""; //
		$join = "";

		if($extraFilter) $filter.= " AND $extraFilter";

		$limit = (isset($_GET['start']) && (int)$_GET['start'] > 0) ? (int)$_GET['start'] : "0";
		$limit .= ", ".$this->ProductsPerPage();

		if(!isset($_GET['sortby'])) {
			$_GET['sortby'] = "";
		}
		$sort = $this->getSortOptionSQL($_GET['sortby']);

		$groupids = array($this->ID);

		if(($recursive === true || $recursive === 'true') && self::$include_child_groups && $childgroups = $this->ChildGroups(true))
			$groupids = array_merge($groupids,$childgroups->map('ID','ID'));

		$groupidsimpl = implode(',',$groupids);

		$join = $this->getManyManyJoin('Products','Product');
		$multicatfilter = $this->getManyManyFilter('Products','Product');

		$products = DataObject::get('Product',"(\"ParentID\" IN ($groupidsimpl) OR $multicatfilter) $filter",$sort,$join,$limit);
		$allproducts = DataObject::get('Product',"\"ParentID\" IN ($groupidsimpl) $filter","",$join);

		//FIXME: this was breaking the "get_only_show_products_that_can_purchase" code below
		//if($allproducts) $products->TotalCount = $allproducts->Count(); //add total count to returned data for 'showing x to y of z products'

		if($products && $products instanceof DataObjectSet) $products->removeDuplicates();

		//FIXME: this removing does not cater for pagination...so you end up with half empty, or fully empty pages in some cases.
		if($products) {
			if(self::get_only_show_products_that_can_purchase()) {
				foreach($products as $product) {
					if(!$product->canPurchase()) {
						$products->remove($product);
					}
				}
			}
		}
		return $products;
	}

	/**
	 *@return Integer
	 **/
	function ProductsPerPage() {
		$productsPagePage = 0;
		if($this->NumberOfProductsPerPage) {
			$productsPagePage = $this->NumberOfProductsPerPage;
		}
		else {
			if($parent = $this->ParentGroup()) {
				$productsPagePage = $parent->ProductsPerPage();
			}
			else {
				$siteConfig = DataObject::get_one("SiteConfig");
				if($siteConfig) {
					$productsPagePage = $siteConfig->NumberOfProductsPerPage;
				}
			}
		}
		return $productsPagePage;
	}

	/**
	 * Return children ProductGroup pages of this group.
	 * @return DataObjectSet
	 */
	function ChildGroups($recursive = false) {
		if($recursive){
			if($children = DataObject::get('ProductGroup', "\"ParentID\" = '$this->ID'")){
				$output = unserialize(serialize($children));
				foreach($children as $group){
					$output->merge($group->ChildGroups($recursive));
				}
				return $output;
			}
			return null;
		}else{
			return DataObject::get('ProductGroup', "\"ParentID\" = '$this->ID'");
		}
	}

	/**
	 *@return DataObject (ProductGroup)
	 **/
	function ParentGroup() {
		return DataObject::get_by_id("ProductGroup", $this->ParentID);
	}


	/**
	 * Recursively generate a product menu.
	 * @return DataObjectSet
	 */
	function GroupsMenu() {
		if($parent = $this->Parent()) {
			return $parent instanceof ProductGroup ? $parent->GroupsMenu() : $this->ChildGroups();
		} else {
			return $this->ChildGroups();
		}
	}


}
class ProductGroup_Controller extends Page_Controller {

	function init() {
		parent::init();
		//ShoppingCart::add_requirements();
		Requirements::themedCSS('ProductGroup');
	}

	/**
	 * Return the products for this group.
	 *
	 *@return DataObjectSet(Products)
	 **/
	public function Products($recursive = true){
	//	return $this->ProductsShowable("\"FeaturedProduct\" = 1",$recursive);
		return $this->ProductsShowable('',$recursive);
	}

	/**
	 * Return products that are featured, that is products that have "FeaturedProduct = 1"
	 *
	 *@return DataObjectSet(Products)
	 */
	function FeaturedProducts($recursive = true) {
		return $this->ProductsShowable("\"FeaturedProduct\" = 1",$recursive);
	}

	/**
	 * Return products that are not featured, that is products that have "FeaturedProduct = 0"
	 *
	 *@return DataObjectSet(Products)
	 */
	function NonFeaturedProducts($recursive = true) {
		return $this->ProductsShowable("\"FeaturedProduct\" = 0",$recursive);
	}

	/**
	 * Provides a dataset of links for sorting products.
	 *
	 *@return DataObjectSet(Name, Link, Current (boolean), LinkingMode)
	 */
	function SortLinks(){
		if(count(ProductGroup::get_sort_options()) <= 0) return null;
		$sort = (isset($_GET['sortby'])) ? Convert::raw2sql($_GET['sortby']) : self::get_sort_options_default();
		$dos = new DataObjectSet();
		foreach(ProductGroup::get_sort_options() as $key => $array){
			$current = ($key == $sort) ? 'current' : false;
			$dos->push(new ArrayData(array(
				'Name' => _t('ProductGroup.SORTBY'.strtoupper(str_replace(' ','',$array['Title'])),$array['Title']),
				'Link' => $this->Link()."?sortby=$key",
				'Current' => $current,
				'LinkingMode' => $current ? "current" : "link"
			)));
		}
		return $dos;
	}

}

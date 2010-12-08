<?php
 /**
  * Product Group is a 'holder' for Products within the CMS
  * It contains functions for versioning child products
  *
  * @package ecommerce
  */
class ProductGroup extends Page {

	public static $db = array(
		'ChildGroupsPermission' => "Enum('Show Only Featured Products,Show All Products')"
	);

	public static $has_one = array();

	public static $has_many = array();

	public static $many_many = array();

	public static $belongs_many_many = array(
		'Products' => 'Product'
	);

	public static $defaults = array();

	public static $casting = array();

	static $default_child = 'Product';

	static $add_action = 'a Product Group Page';

	static $icon = 'cms/images/treeicons/folder';

	protected static $include_child_groups = true;

	protected static $page_length = 12;

	protected static $must_have_price = true;

	//TODO: allow grouping multiple sort fields under one 'sort option', and allow choosing direction of each
	protected static $sort_options = array(
		'Title' => 'Alphabetical',
		'Price' => 'Lowest Price',
		'NumberSold' => 'Most Popular'
		//'Featured' => 'Featured',
		//'Weight' => 'Weight'
	);

	protected static $featured_products_permissions = array(
		'Show Only Featured Products',
		'Show All Products'
	);

	protected static $non_featured_products_permissions = array(
		'Show All Products'
	);


	static function set_include_child_groups($include = true){
		self::$include_child_groups = $include;
	}

	static function set_page_length($length){
		self::$page_length = $length;
	}

	static function set_must_have_price($must = true){
		self::$must_have_price = $must;
	}

	static function set_sort_options(array $options){
		self::$sort_options = $options;
	}

	function get_sort_options(){
		return self::$sort_options;
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();

		if(self::$include_child_groups === 'custom'){
			$fields->addFieldToTab(
				'Root.Content',
				new Tab(
					'Child Groups',
					new HeaderField('How should products be presented in the child groups?'),
					new DropdownField(
	  					'ChildGroupsPermission',
	  					'Permission',
	  					$this->dbObject('ChildGroupsPermission')->enumValues(),
	  					'',
	  					null,
	  					'Don\'t Show Any Products'
					)
				)
			);
		}

		return $fields;
	}

	/**
	 * Returns the shopping cart.
	 * @todo Does HTTP::set_cache_age() still need to be set here?
	 *
	 * @return Order
	 */
	function Cart() {
		HTTP::set_cache_age(0);
		return ShoppingCart::current_order();
	}


	/**
	 * Retrieve a set of products, based on the given parameters. Checks get query for sorting and pagination.
	 *
	 * @param string $extraFilter Additional SQL filters to apply to the Product retrieval
	 * @param array $permissions
	 * @return DataObjectSet
	 */
	 //TODO: optimise this where possible..perhaps use less joins
	function ProductsShowable($extraFilter = '', $recursive = true){
		$filter = ""; //
		$join = "";

		if($extraFilter) $filter.= " AND $extraFilter";
		if(self::$must_have_price) $filter .= " AND \"Price\" > 0";

		$limit = (isset($_GET['start']) && (int)$_GET['start'] > 0) ? (int)$_GET['start'].",".self::$page_length : "0,".self::$page_length;
		$sort = (isset($_GET['sortby'])) ? Convert::raw2sql($_GET['sortby']) : "\"FeaturedProduct\" DESC,\"Title\"";

		//hard coded sort configuration //TODO: make these custom
		if($sort == "NumberSold") $sort .= " DESC";


		$groupids = array($this->ID);

		if(($recursive === true || $recursive === 'true') && self::$include_child_groups && $childgroups = $this->ChildGroups(true))
			$groupids = array_merge($groupids,$childgroups->map('ID','ID'));

		$groupidsimpl = implode(',',$groupids);

		$join = $this->getManyManyJoin('Products','Product');
		$multicatfilter = $this->getManyManyFilter('Products','Product');

		//TODO: get products that appear in child groups (make this optional)

		$products = DataObject::get('Product',"(\"ParentID\" IN ($groupidsimpl) OR $multicatfilter) $filter",$sort,$join,$limit);

		$allproducts = DataObject::get('Product',"\"ParentID\" IN ($groupidsimpl) $filter","",$join);
		
		if($allproducts) $products->TotalCount = $allproducts->Count(); //add total count to returned data for 'showing x to y of z products'
		if($products && $products instanceof DataObjectSet) $products->removeDuplicates();
		return $products;
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

	/**
	 * Automatically creates some ProductGroup pages in
	 * the CMS when the database builds if there hasn't
	 * been any set up yet.
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();

		if(!DataObject::get_one('ProductGroup')) {
			$page1 = new ProductGroup();
			$page1->Title = 'Products';
			$page1->Content = "
				<p>This is the top level products page, it uses the <em>product group</em> page type, and it allows you to show your products checked as 'featured' on it. It also allows you to nest <em>product group</em> pages inside it.</p>
				<p>For example, you have a product group called 'DVDs', and inside you have more product groups like 'sci-fi', 'horrors' or 'action'.</p>
				<p>In this example we have setup a main product group (this page), with a nested product group containing 2 example products.</p>
			";
			$page1->URLSegment = 'products';
			$page1->writeToStage('Stage');
			$page1->publish('Stage', 'Live');
			DB::alteration_message('Product group page \'Products\' created', 'created');

			$page2 = new ProductGroup();
			$page2->Title = 'Example product group';
			$page2->Content = '<p>This is a nested <em>product group</em> within the main <em>product group</em> page. You can add a paragraph here to describe what this product group is about, and what sort of products you can expect to find in it.</p>';
			$page2->URLSegment = 'example-product-group';
			$page2->ParentID = $page1->ID;
			$page2->writeToStage('Stage');
			$page2->publish('Stage', 'Live');
			DB::alteration_message('Product group page \'Example product group\' created', 'created');
		}
	}

}
class ProductGroup_Controller extends Page_Controller {

	function init() {
		parent::init();

		Requirements::themedCSS('ProductGroup');
		Requirements::themedCSS('Cart');
	}

	/**
	 * Return the products for this group.
	 */
	public function Products($recursive = true){
		return $this->ProductsShowable('',$recursive);
	}

	/**
	 * Return products that are featured, that is products that have "FeaturedProduct = 1"
	 */
	function FeaturedProducts($recursive = true) {
		return $this->ProductsShowable("\"FeaturedProduct\" = 1",$recursive);
	}

	/**
	 * Return products that are not featured, that is products that have "FeaturedProduct = 0"
	 */
	function NonFeaturedProducts($recursive = true) {
		return $this->ProductsShowable("\"FeaturedProduct\" = 0",$recursive);
	}

		/**
	 * Provides a dataset of links for sorting products.
	 */
	function SortLinks(){
		if(count(ProductGroup::get_sort_options()) <= 0) return null;

		$sort = (isset($_GET['sortby'])) ? Convert::raw2sql($_GET['sortby']) : "Title";
		$dos = new DataObjectSet();
		foreach(ProductGroup::get_sort_options() as $field => $name){
			$current = ($field == $sort) ? 'current' : false;
			$dos->push(new ArrayData(array(
				'Name' => $name,
				'Link' => $this->Link()."?sortby=$field",
				'Current' => $current
			)));
		}
		return $dos;
	}

}

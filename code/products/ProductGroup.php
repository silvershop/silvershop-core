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
	
	static $featured_products_permissions = array(
		'Show Only Featured Products',
		'Show All Products'
	);
	
	static $non_featured_products_permissions = array(
		'Show All Products'
	);
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
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
	 * Recursively create a set of {@link Product} pages
	 * that belong to this ProductGroup as a child, related
	 * Product, or through one of this ProductGroup's nested
	 * ProductGroup pages.
	 * 
	 * @param string $extraFilter Additional SQL filters to apply to the Product retrieval
	 * @param array $permissions 
	 * @return DataObjectSet
	 */
	function ProductsShowable($extraFilter = '', $permissions = array("Show All Products")) {
		$filter = "`ShowInMenus` = 1";
		if($extraFilter) $filter .= " AND $extraFilter";
		$products = new DataObjectSet();
		
		$childProducts = DataObject::get('Product', "`ParentID` = $this->ID AND $filter");
		$relatedProducts = $this->getManyManyComponents('Products', $filter);
		
		if($childProducts) {
			$products->merge($childProducts);
		}
		
		if($relatedProducts) {
			$products->merge($relatedProducts);
		}
		
		if(in_array($this->ChildGroupsPermission, $permissions)) {
			if($childGroups = $this->ChildGroups()) {
				foreach($childGroups as $childGroup) {
					$products->merge($childGroup->ProductsShowable($extraFilter, $permissions));
				}
			}
		}
		
		$products->removeDuplicates();
		
		return $products;
	}
	
	/**
	 * Return products that are featured, that is
	 * products that have "FeaturedProduct = 1"
	 */
	function FeaturedProducts() {
		return $this->ProductsShowable("`FeaturedProduct` = 1", self::$featured_products_permissions);
	}
	
	/**
	 * Return products that are not featured, that is
	 * products that have "FeaturedProduct = 0"
	 */
	function NonFeaturedProducts() {
		return $this->ProductsShowable("`FeaturedProduct` = 0", self::$non_featured_products_permissions);
	}
		
	/** 
	 * Return children ProductGroup pages of this group.
	 * @return DataObjectSet
	 */
	function ChildGroups() {
		return DataObject::get('ProductGroup', "`ParentID` = '$this->ID' AND `ShowInMenus` = 1");
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
			Database::alteration_message('Product group page \'Products\' created', 'created');
			
			$page2 = new ProductGroup();
			$page2->Title = 'Example product group';
			$page2->Content = '<p>This is a nested <em>product group</em> within the main <em>product group</em> page. You can add a paragraph here to describe what this product group is about, and what sort of products you can expect to find in it.</p>';
			$page2->URLSegment = 'example-product-group';
			$page2->ParentID = $page1->ID;
			$page2->writeToStage('Stage');
			$page2->publish('Stage', 'Live');
			Database::alteration_message('Product group page \'Example product group\' created', 'created');
		}
	}
	
}
class ProductGroup_Controller extends Page_Controller {

	function init() {
		parent::init();

		Requirements::themedCSS('ProductGroup');
		Requirements::themedCSS('Cart');
	}

}
?>
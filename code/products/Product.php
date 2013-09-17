<?php
/**
 * This is a standard Product page-type with fields like
 * Price, Weight, Model and basic management of
 * groups.
 *
 * It also has an associated Product_OrderItem class,
 * an extension of OrderItem, which is the mechanism
 * that links this page type class to the rest of the
 * eCommerce platform. This means you can add an instance
 * of this page type to the shopping cart.
 *
 * @package shop
 */
class Product extends Page implements Buyable{

	public static $db = array(
		'InternalItemID' => 'Varchar(30)', //ie SKU, ProductID etc (internal / existing recognition of product)
		'Model' => 'Varchar(30)',
		
		'CostPrice' => 'Currency', // Wholesale cost of the product to the merchant
		'BasePrice' => 'Currency', // Base retail price the item is marked at.
		
		//physical properties
		'Weight' => 'Decimal(9,2)',
		'Height' => 'Decimal(9,2)',
		'Width' => 'Decimal(9,2)',
		'Depth' => 'Decimal(9,2)',
		
		'FeaturedProduct' => 'Boolean',
		'AllowPurchase' => 'Boolean',
		
		'Popularity' => 'Float' //storage for ClaculateProductPopularity task
	);
	
	
	public static $has_one = array(
		'Image' => 'Image'
	);

	public static $many_many = array(
		'ProductCategories' => 'ProductCategory'
	);

	public static $defaults = array(
		'AllowPurchase' => true,
		'ShowInMenus' => false
	);

	public static $summary_fields = array(
		'InternalItemID','Title','BasePrice','Weight','Model'
	);

	public static $searchable_fields = array(
		'InternalItemID','Title','Weight','Model','BasePrice'
	);
	
	public static $field_labels = array(
		'InternalItemID' => 'SKU',
		'Title' => 'Title',
		'BasePrice' => 'Price'
	);

	public static $singular_name = "Product";
	function i18n_singular_name() { return _t("Product.SINGULAR", $this->stat('singular_name')); }
	public static $plural_name = "Products";
	function i18n_plural_name() { return _t("Product.PLURAL", $this->stat('plural_name')); }
	
	static $icon = 'shop/images/icons/package';
	static $default_parent = 'ProductCategory';
	static $default_sort = '"Title" ASC';
	
	static $order_item = "Product_OrderItem";

	static $number_sold_calculation_type = "SUM"; //SUM or COUNT
	static $global_allow_purchase = true;

	function getCMSFields() {
		self::disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();

		// Standard product detail fields
		$fields->addFieldsToTab('Root.Pricing',array(
			new TextField('BasePrice', _t('Product.PRICE', 'Price - base price to sell this product at'), '', 12),
			new TextField('CostPrice', _t('Product.COSTPRICE', 'Cost Price - wholesale price before markup'), '', 12)
		));
		
		//physical measurements
		$weightunit = "kg"; //TODO: globalise / make custom
		$lengthunit = "cm";  //TODO: globalise / make custom
		$fields->addFieldsToTab('Root.Shipping',array(
			new TextField('Weight', sprintf(_t('Product.WEIGHT', 'Weight (%s)'), $weightunit), '', 12),
			new TextField('Height', sprintf(_t('Product.HEIGHT', 'Height (%s)'), $lengthunit), '', 12),
			new TextField('Width', sprintf(_t('Product.WIDTH', 'Width (%s)'), $lengthunit), '', 12),
			new TextField('Depth', sprintf(_t('Product.DEPTH', 'Depth (%s)'), $lengthunit), '', 12),
		));
		
		$fields->addFieldToTab('Root.Main',new TextField('Model', _t('Product.MODEL', 'Model'), '', 30),'Content');
		$fields->addFieldToTab('Root.Main',new TextField('InternalItemID', _t('Product.CODE', 'Product Code'), '', 30),'Model');
		if(!$fields->dataFieldByName('Image')) {
			$fields->addFieldToTab('Root.Images', new UploadField('Image', _t('Product.IMAGE', 'Product Image')));
		}
		// Flags for this product which affect it's behaviour on the site
		$fields->addFieldToTab('Root.Main',new CheckboxField('FeaturedProduct', _t('Product.FEATURED', 'Featured Product')), 'Content');
		$fields->addFieldToTab('Root.Main',new CheckboxField('AllowPurchase', _t('Product.ALLOWPURCHASE', 'Allow product to be purchased'), 1),'Content');
		$fields->addFieldsToTab('Root.Categories',array(
			new LabelField('ProductCategoriesInstuctions', _t('Product.CATEGORIES',"Select the categories that this product should also show up in")),
			$this->getProductCategoriesTable()
		));
		if($pagename = $fields->fieldByName('Root.Main.Title')){
			$pagename->setTitle(_t('Product.PAGETITLE','Product Page Title'));
		}

		self::enableCMSFieldsExtensions();
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}

	/**
	 * Enables developers to completely turning off the ability to purcahse products.
	 */
	static function set_global_allow_purchase($allow = false){
		self::$global_allow_purchase = $allow;
	}
		
	/**
	 * Helper for creating the product groups table
	 */
	protected function getProductCategoriesTable() {
		//TODO: SS3 has no support for GridField many_many, get more info
		$productCategories = DataObject::get("ProductCategory");
		$itemsTable = new CheckboxSetField("ProductCategories","Product Categories",$productCategories->map("ID", "Title"));
		
		return $itemsTable;
	}

	/**
	 * Returns the shopping cart.
	 * @todo Does HTTP::set_cache_age() still need to be set here?
	 *
	 * @return Order
	 */
	function getCart() {
		if(!self::$global_allow_purchase) return false;
		HTTP::set_cache_age(0);
		return ShoppingCart::curr();
	}

	/**
	 * Conditions for whether a product can be purchased.
	 *
	 * If it has the checkbox for 'Allow this product to be purchased',
	 * as well as having a price, it can be purchased. Otherwise a user
	 * can't buy it.
	 *
	 * Other conditions may be added by decorating with the canPurcahse function
	 *
	 * @return boolean
	 */
	function canPurchase($member = null) {
		if(!self::$global_allow_purchase) return false;
		if(!$this->dbObject('AllowPurchase')->getValue()) return false;
		if(!$this->isPublished()) return false;
		$allowpurchase = false;
		if(DataObject::get_one("ProductVariation","ProductID = ".$this->ID)){ // TODO: I get errors if I have not decorated product.php with variations... method does not exist
			foreach($this->Variations() as $variation){
				if($variation->canPurchase()){
					$allowpurchase = true;
					break;
				}
			}
		}elseif($this->sellingPrice() > 0){
			$allowpurchase = true;
		}
		// Standard mechanism for accepting permission changes from decorators
		$extended = $this->extendedCan('canPurchase', $member);
		if($allowpurchase && $extended !== null){
			$allowpurchase = $extended;
		}
		return $allowpurchase;
	}

	/**
	 * Returns if the product is already in the shopping cart.
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 *
	 * @return boolean
	 */
	function IsInCart() {
		return ($this->Item() && $this->Item()->Quantity > 0) ? true : false;
	}

	/**
	 * Returns the order item which contains the product
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 */
	function Item() {
		$filter = array();
		$this->extend('updateItemFilter',$filter);
		$item = ShoppingCart::singleton()->get($this,$filter);
		if(!$item)
			$item = $this->createItem(0); //return dummy item so that we can still make use of Item
		$this->extend('updateDummyItem',$item);
		return $item;
	}
	
	/**
	 * @see Buyable::createItem()
	 */
	function createItem($quantity = 1, $filter = null){
		$orderitem = $this->stat("order_item");
		$item = new $orderitem();
		$item->ProductID = $this->ID;
		if($filter){
			$item->update($filter); //TODO: make this a bit safer, perhaps intersect with allowed fields
		}
		$item->Quantity = $quantity;
		return $item;
	}
	
	/**
	 * Original price for template usage
	 */
	function getPrice(){
		$currency = ShopConfig::get_site_currency();
		$field = new Money("Price");
		$field->setAmount($this->sellingPrice());
		$field->setCurrency($currency);
		return $field;
	}
	
	function setPrice($val){
		$this->setField("BasePrice", $val);
	}
	
	/**
	 * The raw retail price the visitor will get when they
	 * add to cart. Can include discounts or markups on the base price.
	 */
	function sellingPrice(){
		$price = $this->BasePrice;
		$this->extend("updateSellingPrice",$price); //TODO: this is not ideal, because prices manipulations will not happen in a known order
		if($price < 0)
			$price = 0; //prevent negative sales
		return $price;
	}
	
	function Link(){
		$link = parent::Link();
		$this->extend('updateLink',$link);
		return $link;
	}
	
	//passing on shopping cart links ...is this necessary?? ...why not just pass the cart?
	function addLink() {
		return ShoppingCart_Controller::add_item_link($this);
	}

	function removeLink() {
		return ShoppingCart_Controller::remove_item_link($this);
	}

	function removeallLink() {
		return ShoppingCart_Controller::remove_all_item_link($this);
	}
	
	/**
	* @deprecated use canPurchase instead.
	*/
	function AllowPurchase(){
		return $this->canPurchase();
	}

}

class Product_Controller extends Page_Controller {

	private static $allowed_actions = array(
		'Form',
		'AddProductForm'
	);
	
	public $formclass = "AddProductForm"; //allow overriding the type of form used
	
	function Form(){
		$formclass = $this->formclass;
		$form = new $formclass($this,"Form");
		$this->extend('updateForm',$form);
		return $form;
	}

}

class Product_OrderItem extends OrderItem {

	static $db = array(
		'ProductVersion' => 'Int'
	);

	static $has_one = array(
		'Product' => 'Product'
	);
	
	/**
	 * the has_one join field to identify the buyable
	 */
	static $buyable_relationship = "Product";
	static $disable_versioned = true;

	/**
	 * Get related product
	 *  - live version if in cart, or
	 *  - historical version if order is placed 
	 *
	 * @param boolean $forcecurrent - force getting latest version of the product.
	 * @return Product
	 */
	public function Product($forcecurrent = false) {
		
		//TODO: this might need some unit testing to make sure it compliles with comment description
			//ie use live if inn cart (however I see no logic for checking cart status)
		
		if($this->ProductID && $this->ProductVersion && !$forcecurrent){
			return Versioned::get_version('Product', $this->ProductID, $this->ProductVersion);
		}elseif($this->ProductID && $product = Versioned::get_one_by_stage('Product','Live', "\"Product\".\"ID\"  = ".$this->ProductID)){
			return $product;
		}
		return false;		
	}

	function onPlacement(){
		parent::onPlacement();
		if($product = $this->Product(true)){
			$this->ProductVersion = $product->Version;
		}
	}
	
	function TableTitle() {
		$product = $this->Product();
		$tabletitle = ($product) ? $product->Title : $this->i18n_singular_name();
		$this->extend('updateTableTitle',$tabletitle);
		return $tabletitle;
	}

	function Link() {
		if($product = $this->Product()){
			return $product->Link();
		}
	}

}
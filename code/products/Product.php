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
		'Price' => 'Currency',
		'Weight' => 'Decimal(9,2)',
		'Model' => 'Varchar(30)',
		'FeaturedProduct' => 'Boolean',
		'AllowPurchase' => 'Boolean',
		'InternalItemID' => 'Varchar(30)', //ie SKU, ProductID etc (internal / existing recognition of product)
		'NumberSold' => 'Int' //store number sold, so it doesn't have to be computed on the fly. Used for determining popularity.
	);

	public static $has_one = array(
		'Image' => 'Product_Image'
	);

	public static $many_many = array(
		'ProductCategories' => 'ProductCategory'
	);

	public static $defaults = array(
		'AllowPurchase' => true,
		'ShowInMenus' => false
	);

	public static $summary_fields = array(
		'ID','InternalItemID','Title','Price','Weight','Model','NumberSold'
	);

	public static $searchable_fields = array(
		'ID','Title','InternalItemID','Weight','Model','Price'
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
		//prevent calling updateCMSFields extend function too early
		$tempextvar = $this->get_static('SiteTree','runCMSFieldsExtensions');
		$this->disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();
		if($tempextvar){
			$this->enableCMSFieldsExtensions();
		}
		// Standard product detail fields
		$fields->addFieldToTab('Root.Content.Main',new TextField('Price', _t('Product.PRICE', 'Price'), '', 12),'Content');
		$fields->addFieldToTab('Root.Content.Main',new TextField('Weight', _t('Product.WEIGHT', 'Weight (kg)'), '', 12),'Content');
		$fields->addFieldToTab('Root.Content.Main',new TextField('Model', _t('Product.MODEL', 'Model'), '', 30),'Content');
		$fields->addFieldToTab('Root.Content.Main',new TextField('InternalItemID', _t('Product.CODE', 'Product Code'), '', 30),'Price');
		if(!$fields->dataFieldByName('Image')) {
			$fields->addFieldToTab('Root.Content.Images', new ImageField('Image', _t('Product.IMAGE', 'Product Image')));
		}
		// Flags for this product which affect it's behaviour on the site
		$fields->addFieldToTab('Root.Content.Main',new CheckboxField('FeaturedProduct', _t('Product.FEATURED', 'Featured Product')), 'Content');
		$fields->addFieldToTab('Root.Content.Main',new CheckboxField('AllowPurchase', _t('Product.ALLOWPURCHASE', 'Allow product to be purchased'), 1),'Content');
		$fields->addFieldsToTab(
			'Root.Content.Categories',
			array(
				new LabelField('ProductCategoriesInstuctions', _t('Product.CATEGORIES',"Select the categories that this product should also show up in")),
				$this->getProductCategoriesTable()
			)
		);
		if($pagename = $fields->fieldByName('Root.Content.Main.Title'))
			$pagename->setTitle(_t('Product.PAGENAME','Page/Product Name'));

		if($tempextvar)
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
	 * Recaulculates the number sold for all products. This should be run as a cron job perhaps daily.
	 */
	static function recalculate_numbersold(){
		$ps = singleton('Product');
		$q = $ps->buildSQL("\"Product\".\"AllowPurchase\" = 1");
		$select = $q->select;

		$select['NewNumberSold'] = self::$number_sold_calculation_type."(\"OrderItem\".\"Quantity\") AS \"NewNumberSold\"";

		$q->select($select);
		$q->groupby("\"Product\".\"ID\"");
		$q->orderby("\"NewNumberSold\" DESC");

		$q->leftJoin('Product_OrderItem','"Product"."ID" = "Product_OrderItem"."ProductID"');
		$q->leftJoin('OrderItem','"Product_OrderItem"."ID" = "OrderItem"."ID"');
		$records = $q->execute();
		$productssold = $ps->buildDataObjectSet($records, "DataObjectSet", $q, 'Product');

		//TODO: this could be done faster with an UPDATE query (SQLQuery doesn't support this yet @11/06/2010)
		foreach($productssold as $product){
			if($product->NewNumberSold != $product->NumberSold){
				$product->NumberSold = $product->NewNumberSold;
				$product->writeToStage('Stage');
				$product->publish('Stage', 'Live');
			}
		}

	}
	
	/**
	 * Helper for creating the product groups table
	 */
	protected function getProductCategoriesTable() {
		$tableField = new ManyManyComplexTableField(
			$this,
			'ProductCategories',
			'ProductCategory',
			array(
				'Title' => 'Category'
			)
		);
		$tableField->setPageSize(30);
		$tableField->setPermissions(array());
		return $tableField;
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
		return ShoppingCart::current_order();
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

		if($this->Variations()->exists()){
			foreach($this->Variations() as $variation){
				if($variation->canPurchase()){
					$allowpurchase = true;
					break;
				}
			}
		}elseif($this->Price > 0){
			$allowpurchase = true;
		}

		// Standard mechanism for accepting permission changes from decorators
		$extended = $this->extendedCan('canPurchase', $member);
		if($allowpurchase && $extended !== null) $allowpurchase = $extended;

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
		$filter = null;
		$this->extend('updateItemFilter',$filter);
		$item = ShoppingCart::getInstance()->get($this); //TODO: needs filter
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
	 * Return the currency being used on the site.
	 * @return string Currency code, e.g. "NZD" or "USD"
	 */
	function Currency() {
		if(class_exists('Payment')) {
			return Payment::site_currency();
		}
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

	//Deprecated fields
	
	/**
	* @deprecated v1.0 use canPurchase instead.
	*/
	function AllowPurchase(){
		return $this->canPurchase();
	}

}

class Product_Controller extends Page_Controller {

	static $allowed_actions = array();
	
}

class Product_Image extends Image {

	//default image sizes
	protected static $thumbnail_width = 140;
	protected static $thumbnail_height = 100;
	protected static $content_image_width = 200;
	protected static $large_image_width = 600;

	static function set_thumbnail_size($width = 140, $height = 100){
		self::$thumbnail_width = $width;
		self::$thumbnail_height = $height;
	}

	static function set_content_image_width($width = 200){
		self::$content_image_width = $width;
	}

	static function set_large_image_width($width = 600){
		self::$large_image_width = $width;
	}

	function generateThumbnail($gd) {
		$gd->setQuality(80);
		return $gd->paddedResize(self::$thumbnail_width,self::$thumbnail_height);
	}

	function generateContentImage($gd) {
		$gd->setQuality(90);
		return $gd->resizeByWidth(self::$content_image_width);
	}

	function generateLargeImage($gd) {
		$gd->setQuality(90);
		return $gd->resizeByWidth(self::$large_image_width);
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
	 * Get related product, based on version info if it is not in the cart.
	 *
	 * @param boolean $forcecurrent - force getting latest version of the product.
	 * @return Product
	 */
	public function Product($forcecurrent = false) {
		if($this->ProductID && $this->ProductVersion && !$forcecurrent){
			return FixVersioned::get_version('Product', $this->ProductID, $this->ProductVersion);
		}elseif($this->ProductID && $product = DataObject::get_by_id('Product', $this->ProductID)){
			return $product;
		}
		return false;		
	}

	function UnitPrice() {
		$product = $this->Product();
		$unitprice = ($product) ? $product->Price : 0;
		$this->extend('updateUnitPrice',$unitprice);
		return $unitprice;
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

	function addLink() {
		return ShoppingCart_Controller::add_item_link($this->Product(),$this->linkParameters());
	}

	function removeLink() {
		return ShoppingCart_Controller::remove_item_link($this->Product(),$this->linkParameters());
	}

	function removeallLink() {
		return ShoppingCart_Controller::remove_all_item_link($this->Product(),$this->linkParameters());
	}

	function setquantityLink() {
		return ShoppingCart_Controller::set_quantity_item_link($this->Product(),$this->linkParameters());
	}

	function linkParameters(){
		$array = array();
		$this->extend('updateLinkParameters',$array);
		return $array;
	}
	
	/**
	 * @deprecated - use TableTitle instead
	 */
	function ProductTitle(){
		return $this->TableTitle();
	}

}
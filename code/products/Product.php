<?php
/**
 * This is a standard Product page-type with fields like
 * Price, Weight, Model/Author and basic management of
 * groups.
 * 
 * It also has an associated Product_OrderItem class,
 * an extension of OrderItem, which is the mechanism
 * that links this page type class to the rest of the
 * eCommerce platform. This means you can add an instance
 * of this page type to the shopping cart.
 * 
 * @package ecommerce
 */
class Product extends Page {
	
	public static $db = array(
		'Price' => 'Currency',
		'Weight' => 'Decimal(9,2)',
		'Model' => 'Varchar',
		'FeaturedProduct' => 'Boolean',
		'AllowPurchase' => 'Boolean',
		'InternalItemID' => 'Varchar(30)'
	);
	
	public static $has_one = array(
		'Image' => 'Product_Image'
	);
	
	public static $has_many = array(
		'Variations' => 'ProductVariation'
	);
	
	public static $many_many = array(
		'ProductGroups' => 'ProductGroup'
	);
	
	public static $belongs_many_many = array();
	
	public static $defaults = array(
		'AllowPurchase' => true
	);
	
	public static $casting = array();
	
	static $default_parent = 'ProductGroup';
	
	static $add_action = 'a Product Page';
	
	static $icon = 'cms/images/treeicons/book';
	
	function getCMSFields() {
		$fields = parent::getCMSFields();

		// Standard product detail fields
		$fields->addFieldsToTab(
			'Root.Content.Main',
			array(
				new TextField('Weight', _t('Product.WEIGHT', 'Weight (kg)'), '', 12),
				new TextField('Price', _t('Product.PRICE', 'Price'), '', 12),
				new TextField('Model', _t('Product.MODEL', 'Model/Author'), '', 50),
				new TextField('InternalItemID', _t('Product.CODE', 'Product Code'), '', 7)
			)
		);

		if(!$fields->dataFieldByName('Image')) {
			$fields->addFieldToTab('Root.Content.Images', new ImageField('Image', _t('Product.IMAGE', 'Product Image')));
		}

		// Flags for this product which affect it's behaviour on the site
		$fields->addFieldsToTab(
			'Root.Content.Main',
			array(
				new CheckboxField('FeaturedProduct', _t('Product.FEATURED', 'Featured Product')),
				new CheckboxField('AllowPurchase', _t('Product.ALLOWPURCHASE', 'Allow product to be purchased'), 1)
			)
		);

		$fields->addFieldsToTab(
			'Root.Content.Variations', 
			array(
				new HeaderField(_t('Product.VARIATIONSSET', 'This product has the following variations set')),
				new LiteralField('VariationsNote', '<p class="message good">If this product has active variations, the price of the product will be the price of the variation added by the member to the shopping cart.</p>'),
				$this->getVariationsTable()
			)
		);

		$fields->addFieldsToTab(
			'Root.Content.Product Groups',
			array(
				new HeaderField(_t('Product.ALSOAPPEARS', 'This product also appears in the following groups')),
				$this->getProductGroupsTable()
			)
		);
		
		return $fields;
	}
	
	function getVariationsTable() {
		$singleton = singleton('ProductVariation');
		$query = $singleton->buildVersionSQL("`ProductID` = '{$this->ID}'");
		$variations = $singleton->buildDataObjectSet($query->execute());
		$filter = $variations ? "`ID` IN ('" . implode("','", $variations->column('RecordID')) . "')" : "`ID` < '0'";
		//$filter = "`ProductID` = '{$this->ID}'";
		
		$tableField = new HasManyComplexTableField(
			$this,
			'Variations',
			'ProductVariation',
			array(
				'Title' => 'Title',
				'Price' => 'Price'
			),
			'getCMSFields_forPopup',
			$filter
		);
		
		if(method_exists($tableField, 'setRelationAutoSetting')) {
			$tableField->setRelationAutoSetting(true);
		}
		
		return $tableField;
	}
	
	protected function getProductGroupsTable() {
		$tableField = new ManyManyComplexTableField(
			$this,
			'ProductGroups',
			'ProductGroup',
			array(
				'Title' => 'Product Group Page Title'
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
	function Cart() {
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
	 * @return boolean
	 */
	function AllowPurchase() {
		return $this->AllowPurchase && $this->Price;
	}
	
	/**
	 * Returns if the product is already in the shopping cart.
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 * 
	 * @return boolean
	 */
	function IsInCart() {
		return $this->Item() ? true : false;
	}
	
	/**
	 * Returns the order item which contains the product
	 * Note : This function is usable in the Product context because a
	 * Product_OrderItem only has a Product object in attribute
	 */
	function Item() {
		$currentOrder = ShoppingCart::current_order();
		if($items = $currentOrder->Items()) {
			foreach($items as $item) {
				if($item instanceof Product_OrderItem && $itemProduct = $item->Product()) {
					if($itemProduct->ID == $this->ID && $itemProduct->Version == $this->Version) return $item;
				}
			}
		}
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
	
	/**
	 * Return the global tax information of the site.
	 * @return TaxModifier
	 */
	function TaxInfo() {
		$currentOrder = ShoppingCart::current_order();
		return $currentOrder->TaxInfo();
	}
	
	function addLink() {
		return $this->Link() . 'add';
	}

	function addVariationLink($id) {
		return $this->Link() . 'addVariation/' . $id;
	}
	
	/**
	 * When the ecommerce module is first installed, and db/build
	 * is invoked, create some default records in the database.
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		if(!DataObject::get_one('Product')) {		
			if(!DataObject::get_one('ProductGroup')) singleton('ProductGroup')->requireDefaultRecords();
			if($group = DataObject::get_one('ProductGroup', '', true, '`ParentID` DESC')) {
				$content = '<p>This is a <em>product</em>. It\'s description goes into the Content field as a standard SilverStripe page would have it\'s content. This is an ideal place to describe your product.</p>';
				
				$page1 = new Product();
				$page1->Title = 'Example product';
				$page1->Content = $content . '<p>You may also notice that we have checked it as a featured product and it will be displayed on the main Products page.</p>';
				$page1->URLSegment = 'example-product';
				$page1->ParentID = $group->ID;
				$page1->Price = '15.00';
				$page1->Weight = '0.50';
				$page1->Model = 'Joe Bloggs';
				$page1->FeaturedProduct = true;
				$page1->writeToStage('Stage');
				$page1->publish('Stage', 'Live');
				Database::alteration_message('Product page \'Example product\' created', 'created');
				
				$page2 = new Product();
				$page2->Title = 'Example product 2';
				$page2->Content = $content;
				$page2->URLSegment = 'example-product-2';
				$page2->ParentID = $group->ID;
				$page2->Price = '25.00';
				$page2->Weight = '1.2';
				$page2->Model = 'Jane Bloggs';
				$page2->writeToStage('Stage');
				$page2->publish('Stage', 'Live');
				Database::alteration_message('Product page \'Example product 2\' created', 'created');		
			}
		}
	}
	
}

class Product_Controller extends Page_Controller {
	
	function init() {
		parent::init();
		
		Requirements::themedCSS('Product');
		Requirements::themedCSS('Cart');
	}
	
	function add() {
		if($this->AllowPurchase() && $this->Variations()->Count() == 0) {
			ShoppingCart::add_new_item(new Product_OrderItem($this->dataRecord));
			if(!$this->isAjax()) Director::redirectBack();
		}
	}
	
	function addVariation() {
		if($this->AllowPurchase && $this->urlParams['ID']) {
			$variation = DataObject::get_one(
				'ProductVariation', 
				sprintf(
					"`ID` = %d AND `ProductID` = %d",
					(int)$this->urlParams['ID'],
					(int)$this->ID
				)
			);
			if($variation) {
				if($variation->AllowPurchase()) {
					ShoppingCart::add_new_item(new ProductVariation_OrderItem($variation));
					if(!$this->isAjax()) Director::redirectBack();
				}
			}
		}
	}
	
}
class Product_Image extends Image {

	public static $db = array();
	
	public static $has_one = array();
	
	public static $has_many = array();
	
	public static $many_many = array();
	
	public static $belongs_many_many = array();
	
	function generateThumbnail($gd) {
		$gd->setQuality(80);
		return $gd->paddedResize(140,100);
	}
	
	function generateContentImage($gd) {
		$gd->setQuality(90);
		return $gd->resizeByWidth(200);
	}
	
	function generateLargeImage($gd) {
		$gd->setQuality(90);
		return $gd->resizeByWidth(600);
	}
	
}
class Product_OrderItem extends OrderItem {
	
	protected $_productID;
	
	protected $_productVersion;
	
	static $db = array(
		'ProductVersion' => 'Int'
	);
	
	static $has_one = array(
		'Product' => 'Product'
	);
	
	public function __construct($product = null, $quantity = 1) {
		// Case 1: Constructed by getting OrderItem from DB
		if(is_array($product)) {
			$this->ProductID = $this->_productID = $product['ProductID'];
			$this->ProductVersion = $this->_productVersion = $product['ProductVersion'];
		}
		
		// Case 2: Constructed in memory
		if(is_object($product)) {		
			$this->_productID = $product->ID;
 			$this->_productVersion = $product->Version;
 			
		}
		
 		parent::__construct($product, $quantity);
	}
	
	function getProductID() {
		return $this->_productID;
	}
	
	/**
	 * Overloaded Product accessor method.
	 *  
	 * Overloaded from the default has_one accessor to
	 * retrieve a product by it's version, this is extremely
	 * useful because we can set in stone the version of
	 * a product at the time when the user adds the item to
	 * their cart, so if the CMS admin changes the price, it
	 * remains the same for this order.
	 * 
	 * @param boolean $current If set to TRUE, returns the latest published version of the Product,
	 * 								If set to FALSE, returns the set version number of the Product
	 * 						 		(instead of the latest published version)
	 * @return Product object
	 */
	public function Product($current = false) {
		if($current) return DataObject::get_by_id('Product', $this->_productID);
		else return Versioned::get_version('Product', $this->_productID, $this->_productVersion);
	}
	
	function hasSameContent($orderItem) {
		$equals = parent::hasSameContent($orderItem);
		return $equals && $orderItem instanceof Product_OrderItem && $this->_productID == $orderItem->_productID && $this->_productVersion == $orderItem->_productVersion;
	}
	
	function UnitPrice() {
		return $this->Product()->Price;
	}
	
	function TableTitle() {
		return $this->Product()->Title;
	}

	function Link() {
		if($product = $this->Product(true)) return $product->Link();
	}
	
	function addLink() {
		return ShoppingCart_Controller::add_item_link($this->_productID);
	}
	
	function removeLink() {
		return ShoppingCart_Controller::remove_item_link($this->_productID);
	}
	
	function removeallLink() {
		return ShoppingCart_Controller::remove_all_item_link($this->_productID);
	}
	
	function setquantityLink() {
		return ShoppingCart_Controller::set_quantity_item_link($this->_productID);
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();

		$this->ProductID = $this->_productID;
		$this->ProductVersion = $this->_productVersion;
	}
	
	public function debug() {
		$title = $this->TableTitle();
		$productID = $this->_productID;
		$productVersion = $this->_productVersion;
		return parent::debug() .<<<HTML
			<h3>Product_OrderItem class details</h3>
			<p>
				<b>Title : </b>$title<br/>
				<b>Product ID : </b>$productID<br/>
				<b>Product Version : </b>$productVersion
			</p>
HTML;
	}
}
?>
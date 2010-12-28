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
 * @package ecommerce
 */
class Product extends Page {

	public static $db = array(
		'Price' => 'Currency',
		'Weight' => 'Decimal(9,4)',
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
		'ProductGroups' => 'ProductGroup',
	);

	public static $belongs_many_many = array();

	public static $defaults = array(
		'AllowPurchase' => true
	);

	public static $casting = array();

	public static $summary_fields = array(
		'ID','InternalItemID','Title','Price','Weight','Model','NumberSold'
	);

	public static $searchable_fields = array(
		'ID','Title','InternalItemID','Weight','Model','Price'
	);

	public static $singular_name = 'Product';

	public static $plural_name = 'Products';

	public static $default_parent = 'ProductGroup';

	public static $default_sort = '"Title" ASC';
	
	public static $extensions = array('Buyable');

	public static $add_action = 'a Product Page';

	public static $icon = 'ecommerce/images/icons/package';

	protected static $number_sold_calculation_type = "SUM"; //SUM or COUNT
		static function set_number_sold_calculation_type($allow = false){self::$number_sold_calculation_type = $allow;}
		static function get_number_sold_calculation_type(){return self::$number_sold_calculation_type;}

	/**
	 * Enables developers to completely turning off the ability to purcahse products.
	 */
	protected static $global_allow_purchase = true;
		static function set_global_allow_purchase($v){self::$global_allow_purchase = $v;}
		static function get_global_allow_purchase(){return self::$global_allow_purchase;}

	function getCMSFields() {
		//prevent calling updateCMSFields extend function too early
		$tempextvar = $this->get_static('SiteTree','runCMSFieldsExtensions');
		$this->disableCMSFieldsExtensions();
		$fields = parent::getCMSFields();
		if($tempextvar)	$this->enableCMSFieldsExtensions();

		// Standard product detail fields
		$fields->addFieldToTab('Root.Content.Main',new TextField('Price', _t('Product.PRICE', 'Price'), '', 12),'Content');
		$fields->addFieldToTab('Root.Content.Main',new TextField('Weight', _t('Product.WEIGHT', 'Weight (kg)'), '', 12),'Content');
		$fields->addFieldToTab('Root.Content.Main',new TextField('Model', _t('Product.MODEL', 'Model'), '', 30),'Content');
		$fields->addFieldToTab('Root.Content.Main',new TextField('InternalItemID', _t('Product.CODE', 'Product Code'), '', 30),'Content');

		if(!$fields->dataFieldByName('Image')) {
			$fields->addFieldToTab('Root.Content.Images', new ImageField('Image', _t('Product.IMAGE', 'Product Image')));
		}

		// Flags for this product which affect it's behaviour on the site
		$fields->addFieldToTab('Root.Content.Main',new CheckboxField('FeaturedProduct', _t('Product.FEATURED', 'Featured Product')), 'Content');
		$fields->addFieldToTab('Root.Content.Main',new CheckboxField('AllowPurchase', _t('Product.ALLOWPURCHASE', 'Allow product to be purchased'), 1),'Content');
		$fields->addFieldsToTab(
			'Root.Content.Product Groups',
			array(
				new HeaderField('ProductGroupsHeader', _t('Product.ALSOAPPEARS')),
				$this->getProductGroupsTable()
			)
		);

		if($tempextvar) {
			$this->extend('updateCMSFields', $fields);
		}
		return $fields;
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

		$q->leftJoin('OrderItem','"Product"."ID" = "OrderItem"."BuyableID"');
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

	protected function getProductGroupsTable() {
		$stage = Versioned::current_stage();
		if($stage) {
			$stage = "_".$stage;
		}
		$tableField = new ManyManyComplexTableField(
			$this,
			'ProductGroups',
			'ProductGroup',
			array('Title' => 'Product Group Page Title'),
			$detailFormFields = null,
			$sourceFilter = '`SiteTree'.$stage.'`.`ID` <> '.intval($this->ParentID)
		);
		$tableField->setPageSize(100);
		$tableField->setPermissions(array());
		return $tableField;
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
		/*if($this->ShopClosed()) { //TODO: ShopClosed not found?
			return false;
		}*/
		if(!self::get_global_allow_purchase()) {
			return false;
		}
		if(!$this->dbObject('AllowPurchase')->getValue()) {
			return false;
		}
		$allowpurchase = false;
		if($this->Price > 0){
			$allowpurchase = true;
		}
		// Standard mechanism for accepting permission changes from decorators
		$extended = $this->extendedCan('canPurchase', $member);
		if($allowpurchase && $extended !== null) {
			$allowpurchase = $extended;
		}
		return $allowpurchase;
	}

	/**
	 * When the ecommerce module is first installed, and db/build
	 * is invoked, create some default records in the database.
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();

		if(!DataObject::get_one('Product')) {
			if(!DataObject::get_one('ProductGroup')) singleton('ProductGroup')->requireDefaultRecords();
			if($group = DataObject::get_one('ProductGroup', '', true, "\"ParentID\" DESC")) {
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
				DB::alteration_message('Product page \'Example product\' created', 'created');

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
				DB::alteration_message('Product page \'Example product 2\' created', 'created');
			}
		}
	}

}

class Product_Controller extends Page_Controller {

	function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('ecommerce/javascript/Cart.js');
		Requirements::themedCSS('Product');
		Requirements::themedCSS('Cart');
	}

}

class Product_Image extends Image {

	public static $db = array();

	public static $has_one = array();

	public static $has_many = array();

	public static $many_many = array();

	public static $belongs_many_many = array();

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
		'Keep' => 'Boolean'
	);


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
		return $this->Buyable($current);
	}

	function hasSameContent($orderItem) {
		$parentIsTheSame = parent::hasSameContent($orderItem);
		return $parentIsTheSame && $orderItem instanceof Product_OrderItem;
	}

	function UnitPrice() {
		$unitprice = $this->Product()->Price;
		$this->extend('updateUnitPrice',$unitprice);
		return $unitprice;
	}

	function TableTitle() {
		
		$tabletitle = $this->Product()->Title;
		$this->extend('updateTableTitle',$tabletitle);
		return $tabletitle;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}

	public function debug() {
		$title = $this->TableTitle();
		$productID = $this->BuyableID;
		$productVersion = $this->Version;
		$html = parent::debug() .<<<HTML
			<h3>Product_OrderItem class details</h3>
			<p>
				<b>Title : </b>$title<br/>
				<b>Product ID : </b>$productID<br/>
				<b>Product Version : </b>$productVersion
			</p>
HTML;
		$this->extend('updateDebug',$html);
		return $html;
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		// we must check for individual database types here because each deals with schema in a none standard way
		//can we use Table::has_field ???
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $exist = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='Product_OrderItem' AND column_name = 'ProductVersion'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $exist = DB::query("SHOW COLUMNS FROM \"Product_OrderItem\" LIKE 'ProductVersion'")->numRecords();
		}
 		if($exist > 0) {
			DB::query("
				UPDATE \"OrderItem\", \"Product_OrderItem\"
					SET \"OrderItem\".\"Version\" = \"Product_OrderItem\".\"ProductVersion\"
				WHERE \"OrderItem\".\"ID\" = \"Product_OrderItem\".\"ID\"
			");
			DB::query("
				UPDATE \"OrderItem\", \"Product_OrderItem\"
					SET \"OrderItem\".\"BuyableID\" = \"Product_OrderItem\".\"ProductID\"
				WHERE \"OrderItem\".\"ID\" = \"Product_OrderItem\".\"ID\"
			");
 			DB::query("ALTER TABLE \"Product_OrderItem\" CHANGE COLUMN \"ProductVersion\" \"_obsolete_ProductVersion\" Integer(11)");
 			DB::query("ALTER TABLE \"Product_OrderItem\" CHANGE COLUMN \"ProductID\" \"_obsolete_ProductID\" Integer(11)");
 			DB::alteration_message('made ProductVersion and ProductID obsolete in Product_OrderItem', 'obsolete');
		}

	}


}

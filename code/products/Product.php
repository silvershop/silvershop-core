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

	public static $has_many = array(
		'Variations' => 'ProductVariation'
	);

	public static $many_many = array(
		'ProductGroups' => 'ProductGroup',
		
		'VariationAttributes' => 'ProductAttributeType'
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

	static $default_parent = 'ProductGroup';

	static $default_sort = '"Title" ASC';

	static $add_action = 'a Product Page';

	static $icon = 'ecommerce/images/icons/package';

	static $number_sold_calculation_type = "SUM"; //SUM or COUNT

	static $global_allow_purchase = true;

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
		$fields->addFieldToTab('Root.Content.Main',new TextField('InternalItemID', _t('Product.CODE', 'Product Code'), '', 30),'Price');

		if(!$fields->dataFieldByName('Image')) {
			$fields->addFieldToTab('Root.Content.Images', new ImageField('Image', _t('Product.IMAGE', 'Product Image')));
		}

		// Flags for this product which affect it's behaviour on the site
		$fields->addFieldToTab('Root.Content.Main',new CheckboxField('FeaturedProduct', _t('Product.FEATURED', 'Featured Product')), 'Content');
		$fields->addFieldToTab('Root.Content.Main',new CheckboxField('AllowPurchase', _t('Product.ALLOWPURCHASE', 'Allow product to be purchased'), 1),'Content');

		$fields->addFieldToTab('Root.Content.Variations',new HeaderField("Variations"));
		$fields->addFieldToTab('Root.Content.Variations',$this->getVariationsTable());
		$fields->addFieldToTab('Root.Content.Variations',new HeaderField("Variation Attribute Types"));
		$fields->addFieldToTab('Root.Content.Variations',$this->getVariationAttributesTable());

		if($this->Variations()->exists()){
			$fields->addFieldToTab('Root.Content.Main',new LabelField('variationspriceinstructinos','Price - Because you have one or more variations, the price can be set in the "Variations" tab.'),'Price');
			$fields->removeFieldsFromTab('Root.Content.Main',array('Price','InternalItemID'));
		}

		$fields->addFieldsToTab(
			'Root.Content.Product Groups',
			array(
				new HeaderField('ProductGroupsHeader', _t('Product.ALSOAPPEARS')),
				$this->getProductGroupsTable()
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

	function getVariationsTable() {
		$singleton = singleton('ProductVariation');
		$query = $singleton->buildVersionSQL("\"ProductID\" = '{$this->ID}'");
		$variations = $singleton->buildDataObjectSet($query->execute());
		$filter = $variations ? "\"ID\" IN ('" . implode("','", $variations->column('RecordID')) . "')" : "\"ID\" < '0'";
		//$filter = "\"ProductID\" = '{$this->ID}'";
		
		$summaryfields= $singleton->summaryFields();
		
		if($this->VariationAttributes()->exists())
			foreach($this->VariationAttributes() as $attribute){
				$summaryfields["AttributeProxy.Val".$attribute->Name] = $attribute->Title;
			}
		
		$tableField = new HasManyComplexTableField(
			$this,
			'Variations',
			'ProductVariation',
			$summaryfields,
			null,
			$filter
		);

		if(method_exists($tableField, 'setRelationAutoSetting')) {
			$tableField->setRelationAutoSetting(true);
		}

		return $tableField;
	}
	
	function getVariationAttributesTable(){
		$mmctf = new ManyManyComplexTableField($this,'VariationAttributes','ProductAttributeType');
		
		return $mmctf;
	}
	
	
	function getVariationByAttributes(array $attributes){
		
		if(!is_array($attributes)) return null;
		$keyattributes = array_keys($attributes);
		$id = $keyattributes[0];
		$where = "\"ProductID\" = ".$this->ID;
		$join = "";
		
		foreach($attributes as $typeid => $valueid){
			if(!is_numeric($typeid) || !is_numeric($valueid)) return null; //ids MUST be numeric
			
			$alias = "A$typeid";
			$where .= " AND $alias.ProductAttributeValueID = $valueid";
			$join .= "INNER JOIN ProductVariation_AttributeValues AS $alias ON ProductVariation.ID = $alias.ProductVariationID ";
		}
		$variation = DataObject::get('ProductVariation',$where,"",$join);
		return $variation->First();
		
	}
	
	/*
	 * Generates variations based on selected attributes. 
	 */
	function generateVariationsFromAttributes(ProductAttributeType $attributetype, array $values){
		
		//TODO: introduce transactions here, in case objects get half made etc
		
		//if product has variation attribute types
		if(is_array($values)){
			
			//TODO: get values dataobject set
			$avalues = $attributetype->convertArrayToValues($values);
			
			$existingvariations = $this->Variations();
			
			if($existingvariations->exists()){
				
				//delete old variation, and create new ones - to prevent modification of exising variations
				foreach($existingvariations as $oldvariation){

					$oldvalues = $oldvariation->AttributeValues();
					
					foreach($avalues as $value){
					
						$newvariation = $oldvariation->duplicate();
						$newvariation->InternalItemID = $this->InternalItemID.'-'.$newvariation->ID;
						$newvariation->AttributeValues()->addMany($oldvalues);
						
						
						$newvariation->AttributeValues()->add($value);
						$newvariation->write();
						
						$existingvariations->add($newvariation);
					}
					$existingvariations->remove($oldvariation);
					$oldvariation->AttributeValues()->removeAll();
					$oldvariation->delete();
					$oldvariation->destroy();
					//TODO: check that old variations actually stick around, as they will be needed for past orders etc
				}				
				
			}else{
					
				foreach($avalues as $value){
					$variation = new ProductVariation();
					$variation->ProductID = $this->ID;
					$variation->Price = $this->Price;
					$variation->write();
					$variation->InternalItemID = $this->InternalItemID.'-'.$variation->ID;
					$variation->AttributeValues()->add($value); //TODO: find or create actual value
					$variation->write();
					
					$existingvariations->add($variation);
				}
			}
		}
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

		//TODO: use a tree structure for selecting groups
		//$field = new TreeMultiselectField('ProductGroups','Product Groups','ProductGroup');

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
	 * Depreciated - use canPurchase instead.
	 */
	function AllowPurchase(){
		return $this->canPurchase();
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
		$item = ShoppingCart::get_item_by_id($this->ID,null,$filter); //TODO: needs filter
		if(!$item)
			$item = new Product_OrderItem($this,0); //return dummy item so that we can still make use of Item
		$this->extend('updateDummyItem',$item);
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

	/**
	 * Return the global tax information of the site.
	 * @return TaxModifier
	 */
	function TaxInfo() {
		$currentOrder = ShoppingCart::current_order();
		return $currentOrder->TaxInfo();
	}

	//passing on shopping cart links ...is this necessary?? ...why not just pass the cart?
	function addLink() {
		return ShoppingCart::add_item_link($this->ID);
	}

	function removeLink() {
		return ShoppingCart::remove_item_link($this->ID);
	}

	function removeallLink() {
		return ShoppingCart::remove_all_item_link($this->ID);
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
	
	function onBeforeDelete(){
		parent::onBeforeDelete();
		foreach($this->Variations() as $variation){
			$variation->delete();
			$variation->destroy();
		}
		 //TODO: make this work...otherwise we get rouge variations that could mess up future imports
	}

}

class Product_Controller extends Page_Controller {

	function init() {
		parent::init();
		Requirements::themedCSS('Product');
		Requirements::themedCSS('Cart');
	}
	
	function VariationForm(){
		
		//TODO: cache this form so it doesn't need to be regenerated all the time?
		
		$farray = array();
		$requiredfields = array();
		$attributes = $this->VariationAttributes();
		
		foreach($this->Variations() as $variation){
			
		}
		
		foreach($attributes as $attribute){
			$farray[] = $attribute->getDropDownField("choose $attribute->Label ...",$this->possibleValuesForAttributeType($attribute));//new DropDownField("Attribute_".$attribute->ID,$attribute->Name,);
			$requiredfields[] = "ProductAttributes[$attribute->ID]";
		}
		
		$fields = new FieldSet($farray);
		$fields->push(new NumericField('Quantity','Quantity',1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)
		
		
		
		if(true){ //TODO: make javascript json inclusion optional
			$vararray = array();
			
			if($vars = $this->Variations()){
				foreach($vars as $var){
					$vararray[$var->ID] = $var->AttributeValues()->map('ID','ID');
				}
			}
			
			$fields->push(new HiddenField('VariationOptions','VariationOptions',json_encode($vararray)));
		}
		
		
		$actions = new FieldSet(
			new FormAction('addVariation', _t("Product.ADDLINK","Add this item to cart"))
		);
		
		
		$requiredfields[] = 'Quantity';
		$validator = new RequiredFields($requiredfields);
		
		$form = new Form($this,'VariationForm',$fields,$actions,$validator);
		return $form;
		
	}
	
	function addVariation($data,$form){
		
		//TODO: save form data to session so selected values are not lost
		
		if(isset($data['ProductAttributes']) && $variation = $this->getVariationByAttributes($data['ProductAttributes'])){
			
			$quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int) $data['Quantity'] : 1; 

			//add this one to cart
			ShoppingCart::add_buyable($variation,$quantity);
			
			$form->sessionMessage("Successfully added to cart.","good");
			
		}else{
			//validation fail
			$form->sessionMessage("That variation is not available, sorry.","bad");		
		}
		
		if(!Director::is_ajax()){
			Director::redirectBack();
		}
	}
	
	function possibleValuesForAttributeType($type){
		if(!is_numeric($type))
			$type = $type->ID;
			
		if(!$type) return null;
		
		$where = "TypeID = $type AND ProductVariation.ProductID = $this->ID";
		//TODO: is there a better place to obtain these joins?
		$join = "INNER JOIN ProductVariation_AttributeValues ON ProductAttributeValue.ID = ProductVariation_AttributeValues.ProductAttributeValueID" .
				" INNER JOIN ProductVariation ON ProductVariation_AttributeValues.ProductVariationID = ProductVariation.ID";
		
		$vals = DataObject::get('ProductAttributeValue',$where,$sort = "ProductAttributeValue.Sort,ProductAttributeValue.Value",$join);
		
		return $vals;
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

	function getProductIDForSerialization() {
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
		elseif($this->_productID && $this->_productVersion)
			return Versioned::get_version('Product', $this->_productID, $this->_productVersion);
	}

	function hasSameContent($orderItem) {
		$equals = parent::hasSameContent($orderItem);
		return $equals && $orderItem instanceof Product_OrderItem && $this->_productID == $orderItem->_productID && $this->_productVersion == $orderItem->_productVersion;
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

	function Link() {
		if($product = $this->Product(true)) return $product->Link();
	}

	function addLink() {
		return ShoppingCart::add_item_link($this->_productID,null,$this->linkParameters());
	}

	function removeLink() {
		return ShoppingCart::remove_item_link($this->_productID,null,$this->linkParameters());
	}

	function removeallLink() {
		return ShoppingCart::remove_all_item_link($this->_productID,null,$this->linkParameters());
	}

	function setquantityLink() {
		return ShoppingCart::set_quantity_item_link($this->_productID,null,$this->linkParameters());
	}

	function linkParameters(){
		$array = array();
		$this->extend('updateLinkParameters',$array);
		return $array;
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

}

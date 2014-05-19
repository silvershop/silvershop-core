<?php

/**
 * Product Variation
 *
 * Provides a means for specifying many variations on a product.
 * Used in combination with ProductAttributes, such as color, size.
 * A variation will specify one particular combination, such as red, and large.
 *
 * @package shop
 * @subpackage variations
 */
class ProductVariation extends DataObject implements Buyable {

	private static $db = array(
		'InternalItemID' => 'Varchar(30)',
		'Price' => 'Currency'
	);

	private static $has_one = array(
		'Product' => 'Product',
		'Image' => 'Image'
	);

	private static $many_many = array(
		'AttributeValues' => 'ProductAttributeValue'
	);

	private static $casting = array(
		'Title' => 'Text',
		'Price' => 'Currency'
	);

	private static $versioning = array(
		'Live'
	);

	private static $extensions = array(
		"Versioned('Live')"
	);

	private static $summary_fields = array(
		'InternalItemID' => 'Product Code',
		//'Product.Title' => 'Product',
		'Title' => 'Variation',
		'Price' => 'Price'
	);

	private static $searchable_fields = array(
		'Product.Title',
		'InternalItemID'
	);

	private static $indexes = array(
		'InternalItemID' => true,
		'LastEdited' => true,
	);

	private static $singular_name = "Variation";

	private static $plural_name = "Variations";

	private static $default_sort = "InternalItemID";

	private static $order_item = "ProductVariation_OrderItem";

	private static $title_has_label = true;

	private static $title_separator = ':';

	private static $title_glue = ', ';


	public function getCMSFields() {
		$fields = new FieldList(
			TextField::create('InternalItemID','Product Code'),
			TextField::create('Price')
		);
		//add attributes dropdowns
		$attributes = $this->Product()->VariationAttributeTypes();
		if($attributes->exists()){
			foreach($attributes as $attribute){
				if($field = $attribute->getDropDownField()){
					if($value = $this->AttributeValues()->find('TypeID', $attribute->ID)){
						$field->setValue($value->ID);
					}
					$fields->push($field);
				}else{
					$fields->push(LiteralField::create('novalues'.$attribute->Name,
						"<p class=\"message warning\">".
							$attribute->Name." has no values to choose from.
							You can create them in the \"Products\" &#62;
							\"Product Attribute Type\" section of the CMS.
						</p>"
					));
				}
				//TODO: allow setting custom values here, rather than visiting the products section
			}
		}else{
			$fields->push(LiteralField::create('savefirst',
				"<p class=\"message warning\">".
					"You can choose variation attributes after saving for the first time, if they exist.
				</p>"
			));
		}
		$fields->push(
			UploadField::create('Image', _t('Product.IMAGE', 'Product Image'))
		);
		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	/**
	 * Save selected attributes - somewhat of a hack.
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if(isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])){
			$this->AttributeValues()->setByIDList(array_values($_POST['ProductAttributes']));
		}
	}

	public function getTitle() {
		$values = $this->AttributeValues();
		if($values->exists()) {
			$labelvalues = array();
			foreach($values as $value){
				if(self::config()->title_has_label) {
					$labelvalues[] = $value->Type()->Label . self::config()->title_separator . $value->Value;
				} else {
					$labelvalues[] = $value->Value;
				}
			}

			return implode(self::config()->title_glue, $labelvalues);
		}
		$this->extend('updateTitle', $title);

		return $title;
	}

	//this is used by TableListField to access attribute values.
	public function AttributeProxy(){
		$do = new DataObject();
		if($this->AttributeValues()->exists()){
			foreach($this->AttributeValues() as $value){
				$do->{'Val'.$value->Type()->Name} = $value->Value;
			}
		}
		return $do;
	}

	public function canPurchase($member = null, $quantity = 1) {
		$allowpurchase = false;
		if($product = $this->Product()) {
			$allowpurchase = ($this->sellingPrice() > 0) && $product->AllowPurchase;
		}
		$extended = $this->extendedCan('canPurchase', $member, $quantity);
		if($allowpurchase && $extended !== null) {
			$allowpurchase = $extended;
		}

		return $allowpurchase;
	}

	/*
	 * Returns if the product variation is already in the shopping cart.
	 * @return boolean
	 */
	public function IsInCart() {
		return $this->Item() && $this->Item()->Quantity > 0;
	}

	/*
	 * Returns the order item which contains the product variation
	 * @return  OrderItem
	 */
	public function Item() {
		$filter = array();
		$this->extend('updateItemFilter',$filter);
		$item = ShoppingCart::singleton()->get($this, $filter);
		if(!$item) {
			//return dummy item so that we can still make use of Item
			$item = $this->createItem(0);
		}
		$this->extend('updateDummyItem',$item);
		return $item;
	}

	public function addLink() {
		return $this->Item()->addLink($this->ProductID, $this->ID);
	}

	public function createItem($quantity = 1,$filter = array()){
		$orderitem = self::config()->order_item;
		$item = new $orderitem();
		$item->ProductID = $this->ProductID;
		$item->ProductVariationID = $this->ID;
		//$item->ProductVariationVersion = $this->Version;
		if($filter){
			//TODO: make this a bit safer, perhaps intersect with allowed fields
			$item->update($filter);
		}
		$item->Quantity = $quantity;
		return $item;
	}

	public function sellingPrice(){
		$price = $this->Price;
		if ($price == 0 && $this->Product() && $this->Product()->sellingPrice()){
			$price = $this->Product()->sellingPrice();
		}
		$this->extend("updateSellingPrice",$price);
		//prevent negative values
		$price = $price < 0 ? 0 : $price;

		return $price;
	}

}

/**
 * Product Variation - Order Item
 * Connects a variation to an order, as a line in the order specifying the particular variation.
 * @package shop
 * @subpackage variations
 */
class ProductVariation_OrderItem extends Product_OrderItem {

	private static $db = array(
		'ProductVariationVersion' => 'Int'
	);

	private static $has_one = array(
		'ProductVariation' => 'ProductVariation'
	);

	private static $buyable_relationship = "ProductVariation";

	/**
	 * Overloaded relationship, for getting versioned variations
	 * @param boolean $current
	 */
	public function ProductVariation($forcecurrent = false) {
		if($this->ProductVariationID && $this->ProductVariationVersion && !$forcecurrent){
			return Versioned::get_version('ProductVariation', $this->ProductVariationID, $this->ProductVariationVersion);
		}elseif($this->ProductVariationID && $product = DataObject::get_by_id('ProductVariation', $this->ProductVariationID)){
			return $product;
		}
		return false;
	}

	public function SubTitle() {
		if($this->ProductVariation())
			return $this->ProductVariation()->getTitle();
		return false;
	}

	public function Image(){
		if(($this->ProductVariation()) && $this->ProductVariation()->Image()->exists()){
			return $this->ProductVariation()->Image();
		}
		return $this->Product()->Image();
	}

}

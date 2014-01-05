<?php
/**
 * Adds extra fields and relationships to Products for variations support.
 * 
 * @package shop
 * @subpackage variations
 */
class ProductVariationDecorator extends DataExtension{
	
	static $has_many = array(
		'Variations' => 'ProductVariation'
	);
	
	static $many_many = array(
		'VariationAttributeTypes' => 'ProductAttributeType'
	);

	/**
	 * Adds variations specific fields to the CMS.
	 */
	public function updateCMSFields(FieldList $fields) {
		$productVariationAttributeTypes = DataObject::get("ProductAttributeType")->map("ID", "Title");
		$fields->addFieldToTab('Root.Variations',$this->getVariationsTable());
		$fields->addFieldToTab('Root.Variations',new HeaderField("Variation Attribute Types"));
		$fields->addFieldToTab('Root.Variations',new CheckboxSetField("VariationAttributeTypes","Variation Attribute Types",$productVariationAttributeTypes));
		
		if($this->owner->Variations()->exists()){
			$fields->addFieldToTab('Root.Pricing',new LabelField('variationspriceinstructinos','Price - Because you have one or more variations, the price can be set in the "Variations" tab.'));
			$fields->removeFieldFromTab('Root.Pricing','BasePrice');
			$fields->removeFieldFromTab('Root.Pricing','CostPrice');
			$fields->removeFieldFromTab('Root.Main','InternalItemID');
		}
	}
	
	/**
	 * CMS fields helper function for getting the variations table.
	 * @return HasManyComplexTableField
	 */
	function getVariationsTable() {
		$variations = $this->owner->Variations();
		$itemsConfig = new GridFieldConfig_RelationEditor();
		$itemsTable = new GridField("Variations","Variations",$variations,$itemsConfig);
		return $itemsTable;
	}

	function PriceRange(){
		$maxprice = $minprice = $averageprice = $hasrange = null;
		$variations = $this->owner->Variations();
		if($variations->exists() && $variations->Count()){
			$prices = $variations->map('ID','Price');
			$count = count($prices);
			$sum = array_sum($prices);
			$maxprice = max($prices);
			$minprice = min($prices);
			$hasrange = ($minprice != $maxprice);

			$maxprice = ShopCurrency::create($maxprice);
			$minprice = ShopCurrency::create($minprice);

			if($count > 0){
				$averageprice = $sum/$count;
				$averageprice = ShopCurrency::create($averageprice);
			}
		}else{
			return null;
		}
		return new ArrayData(array(
			'HasRange' => $hasrange,
			'Max' => $maxprice,
			'Min' => $minprice,
			'Average' => $averageprice,
			'Currency' => $this->owner->Currency()
		));
	}

	/**
	 * Pass an array of attribute ids to query for the appropriate variation.
	 * @param array $attributes
	 * @return NULL
	 */
	function getVariationByAttributes(array $attributes){
		if(!is_array($attributes)) return null;
		$keyattributes = array_keys($attributes);
		$id = $keyattributes[0];
		$variations = ProductVariation::get()->filter("ProductID",$this->owner->ID);
		foreach($attributes as $typeid => $valueid){
			if(!is_numeric($typeid) || !is_numeric($valueid))
				return null; //ids MUST be numeric
			$alias = "A$typeid";
			$variations = $variations->innerJoin(
				"ProductVariation_AttributeValues",
				"\"ProductVariation\".\"ID\" = \"$alias\".\"ProductVariationID\"",
				$alias
			)->where("\"$alias\".\"ProductAttributeValueID\" = $valueid");
		}
		if($variation = $variations->First())
			return $variation;
		return false;
	}

	/**
	 * Generates variations based on selected attributes.
	 * 
	 * @param ProductAttributeType $attributetype
	 * @param array $values
	 */
	function generateVariationsFromAttributes(ProductAttributeType $attributetype, array $values){
		//TODO: introduce transactions here, in case objects get half made etc
		//if product has variation attribute types
		if(is_array($values)){
			//TODO: get values dataobject set
			$avalues = $attributetype->convertArrayToValues($values);
			$existingvariations = $this->owner->Variations();
			if($existingvariations->exists()){
				//delete old variation, and create new ones - to prevent modification of exising variations
				foreach($existingvariations as $oldvariation){
					$oldvalues = $oldvariation->AttributeValues();
					foreach($avalues as $value){
						$newvariation = $oldvariation->duplicate();
						$newvariation->InternalItemID = $this->owner->InternalItemID.'-'.$newvariation->ID;
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
					$variation->ProductID = $this->owner->ID;
					$variation->Price = $this->owner->BasePrice;
					$variation->write();
					$variation->InternalItemID = $this->owner->InternalItemID.'-'.$variation->ID;
					$variation->AttributeValues()->add($value); //TODO: find or create actual value
					$variation->write();
					$existingvariations->add($variation);
				}
			}
		}
	}
	
	/**
	 * Get all the values for a given attribute type,
	 * based on this product's variations.
	 */
	function possibleValuesForAttributeType($type){
		if(!is_numeric($type))
			$type = $type->ID;
		if(!$type) return null;
		
		return ProductAttributeValue::get()
					->innerJoin("ProductVariation_AttributeValues",
							"\"ProductAttributeValue\".\"ID\" = \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\""
					)->innerJoin("ProductVariation",
							"\"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\""
					)->where("TypeID = $type AND \"ProductVariation\".\"ProductID\" = ".$this->owner->ID);
	}

	/**
	 * Make sure variations are deleted with product.
	 */
	function onAfterDelete(){
		$remove = false;

		// if a record is staged or live, leave it's variations alone.
		if(!property_exists($this, 'owner')) {
			$remove = true;
		}
		else {

			$staged = Versioned::get_by_stage($this->owner->ClassName, 'Stage')->byID($this->owner->ID);
			$live = Versioned::get_by_stage($this->owner->ClassName, 'Live')->byID($this->owner->ID);

			if(!$staged && !$live) {
				$remove = true;
			}	
		}

		if($remove) {

			foreach($this->owner->Variations() as $variation){
				$variation->delete();
				$variation->destroy();
			}
		}
	}
	
	function contentcontrollerInit($controller){
		if($this->owner->Variations()->exists()){
			$controller->formclass = 'VariationForm';
		}
	}

}

/**
 * 
 * @subpackage variations
 */
class ProductControllerVariationExtension extends Extension{

	/**
	 * @deprecated - use Form instead
	 */
	function VariationForm(){
		return $this->owner->Form();
	}
	
	/**
	 * @deprecated - use Form instead
	 */
	function AddVariationForm(){
		return $this->owner->Form();
	}
	
}
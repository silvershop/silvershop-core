<?php
/**
 * Adds extra fields and relationships to Products for variations support.
 *
 * @package shop
 * @subpackage variations
 */
class ProductVariationsExtension extends DataExtension {

	private static $has_many = array(
		'Variations' => 'ProductVariation'
	);

	private static $many_many = array(
		'VariationAttributeTypes' => 'ProductAttributeType'
	);

	/**
	 * Adds variations specific fields to the CMS.
	 */
	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldsToTab('Root.Variations',array(
			ListboxField::create("VariationAttributeTypes", "Attributes", 
				ProductAttributeType::get()->map("ID", "Title")->toArray()
			)->setMultiple(true)
			->setDescription("These are fields to indicate the way(s) each variation varies. Once selected, they can be edited on each variation."),
			GridField::create("Variations","Variations",
				$this->owner->Variations(),
				GridFieldConfig_RecordEditor::create()
			)
		));
		if($this->owner->Variations()->exists()) {
			$fields->addFieldToTab('Root.Pricing',
				LabelField::create('variationspriceinstructinos','
					Price - Because you have one or more variations, the price can be set in the "Variations" tab.'
				)
			);
			$fields->removeFieldFromTab('Root.Pricing','BasePrice');
			$fields->removeFieldFromTab('Root.Pricing','CostPrice');
			$fields->removeFieldFromTab('Root.Main','InternalItemID');
		}
	}

	public function PriceRange(){
		$variations = $this->owner->Variations();
		if(!$variations->exists() || !$variations->Count()){
			return null;
		}
		$prices = $variations->map('ID','Price')->toArray();
		$pricedata = array(
			'HasRange' => false,
			'Max' => ShopCurrency::create(),
			'Min' => ShopCurrency::create(),
			'Average' => ShopCurrency::create()
		);
		$count = count($prices);
		$sum = array_sum($prices);
		$maxprice = max($prices);
		$minprice = min($prices);
		$pricedata['HasRange'] = ($minprice != $maxprice);
		$pricedata['Max']->setValue($maxprice);
		$pricedata['Min']->setValue($minprice);
		if($count > 0){
			$pricedata['Average']->setValue($sum/$count);
		}

		return new ArrayData($pricedata);
	}

	/**
	 * Pass an array of attribute ids to query for the appropriate variation.
	 * @param array $attributes
	 * @return NULL
	 */
	public function getVariationByAttributes(array $attributes){
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
	public function generateVariationsFromAttributes(ProductAttributeType $attributetype, array $values){
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
	 * Get all the {@link ProductAttributeValue} for a given attribute type, 
	 * based on this product's variations.
	 *
	 * @return DataList
	 */
	public function possibleValuesForAttributeType($type){
		if(!is_numeric($type)) {
			$type = $type->ID;
		}

		if(!$type) {
			return null;
		}

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
	public function onAfterDelete(){
		$remove = false;
		// if a record is staged or live, leave it's variations alone.
		if(!property_exists($this, 'owner')) {
			$remove = true;
		}else {
			$staged = Versioned::get_by_stage($this->owner->ClassName, 'Stage')
						->byID($this->owner->ID);
			$live = Versioned::get_by_stage($this->owner->ClassName, 'Live')
						->byID($this->owner->ID);
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

	public function contentcontrollerInit($controller){
		if($this->owner->Variations()->exists()){
			$controller->formclass = 'VariationForm';
		}
	}

}

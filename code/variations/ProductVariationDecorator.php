<?php

class ProductVariationDecorator extends DataObjectDecorator{

	function extraStatics(){
		return array(
			'has_many' => array(
				'Variations' => 'ProductVariation'
			),
			'many_many' => array(
				'VariationAttributes' => 'ProductAttributeType'
			)
		);
	}

	/**
	 * Adds variations specific fields to the CMS.
	 */
	function updateCMSFields(&$fields){
		$fields->addFieldToTab('Root.Content.Variations',new HeaderField("Variations"));
		$fields->addFieldToTab('Root.Content.Variations',$this->getVariationsTable());
		$fields->addFieldToTab('Root.Content.Variations',new HeaderField("Variation Attribute Types"));
		$fields->addFieldToTab('Root.Content.Variations',new ManyManyComplexTableField($this->owner,'VariationAttributes','ProductAttributeType'));

		if($this->owner->Variations()->exists()){
			$fields->addFieldToTab('Root.Content.Main',new LabelField('variationspriceinstructinos','Price - Because you have one or more variations, the price can be set in the "Variations" tab.'),'Price');
			$fields->removeFieldsFromTab('Root.Content.Main',array('Price','InternalItemID'));
		}
	}

	/**
	 * CMS fields helper function for getting the variations table.
	 * @return HasManyComplexTableField
	 */
	function getVariationsTable() {
		$singleton = singleton('ProductVariation');
		$query = $singleton->buildVersionSQL("\"ProductID\" = '{$this->owner->ID}'");
		$variations = $singleton->buildDataObjectSet($query->execute());
		$filter = $variations ? "\"ID\" IN ('" . implode("','", $variations->column('RecordID')) . "')" : "\"ID\" < '0'";

		$summaryfields= $singleton->summaryFields();

		if($this->owner->VariationAttributes()->exists())
		foreach($this->owner->VariationAttributes() as $attribute){
			$summaryfields["AttributeProxy.Val".$attribute->Name] = $attribute->Title;
		}

		$tableField = new HasManyComplexTableField(
			$this->owner,
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

			$maxprice = DBField::create("Currency",$maxprice);
			$minprice = DBField::create("Currency",$minprice);

			if($count > 0){
				$averageprice = $sum/$count;
				$averageprice = DBField::create("Currency",$averageprice);
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
		$where = "\"ProductID\" = ".$this->owner->ID;
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

	/**
	 * Generates variations based on selected attributes.
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
					$variation->Price = $this->Price;
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
	 * Make sure variations are deleted with product.
	 */
	function onBeforeDelete(){
		foreach($this->owner->Variations() as $variation){
			$variation->delete();
			$variation->destroy();
		}
		//TODO: make this work...otherwise we get rouge variations that could mess up future imports
	}

}

class ProductControllerVariationExtension extends Extension{

	static $max_quantity = 50;

	public static $allowed_actions = array(
		'VariationForm',
		'addVariation'
	);

	function VariationForm(){
		$farray = array();
		$requiredfields = array();
		$attributes = $this->owner->VariationAttributes();

		foreach($attributes as $attribute){
			$farray[] = $attribute->getDropDownField("choose $attribute->Label ...",$this->owner->possibleValuesForAttributeType($attribute));
			$requiredfields[] = "ProductAttributes[$attribute->ID]";
		}

		$fields = new FieldSet($farray);

		if($maxquantity = self::$max_quantity){
			$values = array();
			$count = 1;
			while($count <= $maxquantity){
				$values[$count] = $count;
				$count++;
			}
			$fields->push(new DropdownField('Quantity','Quantity',$values,1));
		}else{
			$fields->push(new NumericField('Quantity','Quantity',1));
		}

		if(true){
			//TODO: make javascript json inclusion optional
			$vararray = array();
			if($vars = $this->owner->Variations()){
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

		$form = new Form($this->owner,'VariationForm',$fields,$actions,$validator);
		return $form;
	}

	function addVariation($data,$form){
		if(isset($data['ProductAttributes']) && $variation = $this->owner->getVariationByAttributes($data['ProductAttributes'])){
			$quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int) $data['Quantity'] : 1;
			ShoppingCart::add_buyable($variation,$quantity); //add this one to cart
			$form->sessionMessage("Successfully added to cart.","good");
		}else{
			$form->sessionMessage("That variation is not available, sorry.","bad"); //validation fail
		}
		if(!Director::is_ajax()){
			Director::redirectBack();
		}
	}

	function possibleValuesForAttributeType($type){
		if(!is_numeric($type))
		$type = $type->ID;

		if(!$type) return null;

		$where = "TypeID = $type AND \"ProductVariation\".\"ProductID\" = ".$this->owner->ID;
		//TODO: is there a better place to obtain these joins?
		$join = "INNER JOIN \"ProductVariation_AttributeValues\" ON \"ProductAttributeValue\".\"ID\" = \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\"" .
					" INNER JOIN \"ProductVariation\" ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"";

		return DataObject::get('ProductAttributeValue',$where,$sort = "\"ProductAttributeValue\".\"Sort\",\"ProductAttributeValue\".\"Value\"",$join);
	}

}
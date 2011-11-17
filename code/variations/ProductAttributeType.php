<?php

class ProductAttributeType extends DataObject{

	static $db = array(
		'Name' => 'Varchar', //for back-end use
		'Label' => 'Varchar' //for front-end use
	);

	static $has_one = array();

	static $has_many = array(
		'Values' => 'ProductAttributeValue'
	);

	static $summary_fields = array(
		'Name' => 'Name'
	);

	static $default_sort = "ID ASC";

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeFieldFromTab('Root.Values','Values');
		$fieldList = array(
			'Value' => 'Value',
			'Sort' => 'Sort'
		);
		$fieldTypes = array(
			'Value' => 'TextField',
			'Sort' => 'TextField'
		);
		$fields->addFieldToTab("Root.Values", new TableField("Values", "ProductAttributeValue",$fieldList,$fieldTypes));
		return $fields;
	}

	static function find_or_make($name){
		$name = strtolower($name);
		if($type = DataObject::get_one('ProductAttributeType',"LOWER(\"Name\") = '$name'"))
			return $type;

		$type = new ProductAttributeType();
		$type->Name = $name;
		$type->Label = $name;
		$type->write();

		return $type;
	}

	function addValues(array $values){
		$avalues = $this->convertArrayToValues($values);
		$this->Values()->addMany($avalues);
	}

	function convertArrayToValues(array $values){
		$set = new DataObjectSet();

		foreach($values as $value){
			$val = $this->Values()->find('Value',$value);
			if(!$val){  //TODO: ignore case, if possible
				$val = new ProductAttributeValue();
				$val->Value = $value;
				$val->write();
			}
			$set->push($val);
		}

		return $set;
	}

	function getDropDownField($emptystring = null,$values = null){

		$values = ($values) ? $values : $this->Values('','Sort ASC, Value ASC');

		if($values->exists()){
			$field = new DropdownField('ProductAttributes['.$this->ID.']',$this->Name,$values->map('ID','Value'));
			if($emptystring)
				$field->setEmptyString($emptystring);
			return $field;
		}
		return null;
	}

	function canDelete(){
		//TODO: prevent deleting if has been used
		return true;
	}

}
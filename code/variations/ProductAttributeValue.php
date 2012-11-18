<?php
/**
 * Product Attribute Value
 * The actual values for a type of product attribute.
 * eg: red, green, blue... 12, 13, 15
 * @subpackage variations
 */
class ProductAttributeValue extends DataObject{

	static $db = array(
		'Value' => 'Varchar',
		'Sort' => 'Int'
	);

	static $has_one = array(
		'Type' => 'ProductAttributeType'
	);

	static $has_many = array();

	static $belongs_to = array();

	static $belongs_many_many = array(
		'ProductVariation' => 'ProductVariation'
	);

	static $summary_fields = array(
		'Value' => 'Value'
	);
	
	static $table_fields = array(
		'Value' => 'Value',
		'Sort' => 'Sort'
	);
	
	static $table_type_fields = array(
		'Value' => 'TextField',
		'Sort' => 'TextField'
	);

	static $default_sort = "TypeID ASC, Sort ASC, Value ASC";
	
	public function tableFields(){
		$fields = self::$table_fields;
		$this->extend("updateTableFields", $fields);
		return $fields;
	}
	
	public function tableTypeFields(){
		$fields = self::$table_type_fields;
		$this->extend("updateTableTypeFields", $fields);
		return $fields;
	}
}
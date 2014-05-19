<?php
/**
 * Product Attribute Value
 * The actual values for a type of product attribute.
 * eg: red, green, blue... 12, 13, 15
 * @subpackage variations
 */
class ProductAttributeValue extends DataObject{

	private static $db = array(
		'Value' => 'Varchar',
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Type' => 'ProductAttributeType'
	);

	private static $belongs_many_many = array(
		'ProductVariation' => 'ProductVariation'
	);

	private static $summary_fields = array(
		'Value' => 'Value',
	);

	private static $indexes = array(
		'LastEdited' => true,
		'Sort' => true,
	);

	private static $default_sort = "TypeID ASC, Sort ASC, Value ASC";

	private static $singular_name = "Value";
	private static $plural_name = "Values";

	public function getCMSFields(){
		$fields = $this->scaffoldFormFields();
		$fields->removeByName("TypeID");
		$fields->removeByName("Sort");
		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

}

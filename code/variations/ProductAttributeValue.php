<?php
class ProductAttributeValue extends DataObject{

	static $db = array(
		'Value' => 'Varchar',
		'Sort' => 'Int'
	);

	static $has_one = array(
		'Type' => 'ProductAttributeType'
	);

	static $has_many = array();

	static $belongs_to = array(

	);

	static $belongs_many_many = array(
		'ProductVariation' => 'ProductVariation'
	);

	static $summary_fields = array(
		'Value' => 'Value',

	);

	static $default_sort = "TypeID ASC, Sort ASC, Value ASC";
}
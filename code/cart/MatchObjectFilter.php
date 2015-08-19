<?php

/**
 * Helper class to create a filter for matching a dataobject,
 * using field values or relationship ids and only those ids.
 *
 * Combining fields defines a way to uniquely identify an object.
 *
 * Useful for finding if a dataobject with given field values exists.
 * Protects against SQL injection, and searching on unauthroised fields.
 * Ignores fields that don't exist on the object.
 * Adds IS NULL, or = 0 for values that are not passed.
 *
 * Similar to SearchContext
 *
 * Conjunctive query
 *
 * Example input:
 * $data = array(
 * 		'FieldName' => 'data'
 * 		'AnotherField' => 32,
 * 		'NotIncludedField' => 'blah'
 * );
 *
 * $required = array(
 * 		'FieldName',
 * 		'AnotherField',
 * 		'ARequiredField'
 * );
 *
 * Example output:
 * "FieldName" = 'data' AND  "AnotherField" = 32 AND "ARequiredField" IS NULL
 *
 */
class MatchObjectFilter{

	protected $className;
	protected $data;
	protected $required;

	/**
	 * @param string $className
	 * @param array $data field values to use
	 * @param array $requiredfields fields required to be included in the query
	 */
	public function __construct($className, array $data, array $requiredfields) {
		$this->className = $className;
		$this->required = $requiredfields;
		$this->data = $data;
	}

	/**
	 * Create SQL where filter
	 * @return array of filter statements
	 */
	public function getFilter() {
		if(!is_array($this->data)){
			return null;
		}
		$singleton = singleton($this->className);
		$hasones = $singleton->has_one();

		$db = $singleton->db();
		$allowed = array_keys(array_merge($db, $hasones)); //fields that can be used
		$fields = array_flip(array_intersect($allowed, $this->required));

		//add 'ID' to has one relationship fields
		foreach($hasones as $key => $value){
			if(isset($fields[$key])){
				$fields[$key."ID"] = $value;
				unset($fields[$key]);
			}
		}

		$new = array();
		foreach($fields as $field => $value){
			$field = Convert::raw2sql($field);
			if(array_key_exists($field, $db)){
				if(isset($this->data[$field])){
					$dbfield = $singleton->dbObject($field);
					$value = $dbfield->prepValueForDB($this->data[$field]);	//product correct format for db values
					$new[] = "\"$field\" = '$value'";
				}else{
					$new[] = "\"$field\" IS NULL";
				}
			}else{
				if(isset($this->data[$field])){
					$value = Convert::raw2sql($this->data[$field]);
					$new[] = "\"{$field}\" = '$value'";
				}else{
					$new[] = "(\"{$field}\" = 0 OR \"$field\" IS NULL)";
				}

			}
		}
		return $new;
	}

}

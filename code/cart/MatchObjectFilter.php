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
 * "FieldName" = 'data' AND  "AnotherField" = 32 AND ("ARequiredField" = 0 OR "ARequiredField" IS NULL)
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
		$this->setRequiredFields($requiredfields);
		$this->setData($data);
	}

	/**
	 * Set required fields
	 * Adds ID to fields that are has_one fields
	 * @param array $fields
	 */
	public function setRequiredFields($fields) {
		$hasones = singleton($this->className)->has_one();
		$idfields = array_intersect($fields, array_keys($hasones));
		$fields = array_diff($fields, $idfields);
		foreach($idfields as $field){
			$fields[] = $field."ID";
		}
		$this->required = $fields;
	}

	/**
	 * Set data
	 * Any required data that is missing will set to null.
	 * Any data that is not required is removed.
	 * @param array $data
	 */
	public function setData($data) {
		$data = array_merge(array_fill_keys($this->required, null), $data);
		$this->data = array_intersect_key($data, array_flip($this->required));
	}

	/**
	 * Filter a given list to exactly match required fields,
	 * including matching non-specfied data on 0 or NULL.
	 */
	public function filterList(DataList $list) {
		if(!is_array($this->data)){
			return null;
		}
		foreach($this->data as $field => $value){
			if($value === null){
				//null required data must be empty/null in db
				$list = $list->where("\"{$field}\" = 0 OR \"$field\" IS NULL");
			}else{
				//force false to be 0 in SQL
				$value = $value === false ? 0 : $value;
				$list = $list->filter($field, $value);
			}
		}

		return $list;
	}

}

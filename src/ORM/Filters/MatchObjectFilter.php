<?php

namespace SilverShop\ORM\Filters;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;

//TODO: Rewrite this with ORM in mind… reduce the amount of raw queries.

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
 *        'FieldName' => 'data'
 *        'AnotherField' => 32,
 *        'NotIncludedField' => 'blah'
 * );
 *
 * $required = array(
 *        'FieldName',
 *        'AnotherField',
 *        'ARequiredField'
 * );
 *
 * Example output:
 * "FieldName" = 'data' AND  "AnotherField" = 32 AND "ARequiredField" IS NULL
 */
class MatchObjectFilter
{
    protected $className;

    protected $data;

    protected $required;

    /**
     * @param string $className
     * @param array  $data           field values to use
     * @param array  $requiredfields fields required to be included in the query
     */
    public function __construct($className, array $data, array $requiredfields)
    {
        $this->className = $className;
        $this->required = $requiredfields;
        $this->data = $data;
    }

    /**
     * Create SQL where filter
     *
     * @return array of filter statements
     */
    public function getFilter()
    {
        if (!is_array($this->data)) {
            return null;
        }
        $allowed = array_keys(DataObject::getSchema()->databaseFields($this->className));
        $fields = array_flip(array_intersect($allowed, $this->required));
        $singleton = singleton($this->className);

        $new = [];
        foreach ($fields as $field => $value) {
            $field = Convert::raw2sql($field);
            if (in_array($field, $allowed)) {
                if (isset($this->data[$field])) {
                    // allow wildcard in filter
                    if ($this->data[$field] === '*') {
                        continue;
                    }

                    $dbfield = $singleton->dbObject($field);
                    $value = $dbfield->prepValueForDB($this->data[$field]);

                    $new[] = "\"$field\" = '$value'";
                } else {
                    $new[] = "\"$field\" IS NULL";
                }
            } else {
                if (isset($this->data[$field])) {
                    $value = Convert::raw2sql($this->data[$field]);
                    $new[] = "\"$field\" = '$value'";
                } else {
                    $new[] = "\"$field\" IS NULL";
                }
            }
        }
        return $new;
    }
}

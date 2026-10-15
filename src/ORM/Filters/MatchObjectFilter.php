<?php

declare(strict_types=1);

namespace SilverShop\ORM\Filters;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBForeignKey;

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
 * has_one relations may be given with or without the "ID" suffix.
 * Adds IS NULL for values that are not passed, or = 0 OR IS NULL for has_one IDs.
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

    protected array $data;

    protected array $required;

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
     * Normalise a field name so that has_one relations can be given either
     * with or without the "ID" suffix, e.g. "Recipient" becomes "RecipientID".
     * Any other field name is returned unchanged.
     */
    public static function normaliseFieldName(string $className, string $field): string
    {
        if (DataObject::getSchema()->hasOneComponent($className, $field)) {
            return $field . 'ID';
        }

        return $field;
    }

    /**
     * Create SQL where filter
     *
     * @return array of filter statements
     */
    public function getFilter(): ?array
    {
        if (!is_array($this->data)) {
            return null;
        }

        $data = $this->data;
        foreach ($this->data as $field => $value) {
            $normalised = static::normaliseFieldName($this->className, (string) $field);
            // an explicit "XID" key wins over the "X" shorthand
            if ($normalised !== $field && !array_key_exists($normalised, $data)) {
                $data[$normalised] = $value;
            }
        }

        $required = [];
        foreach ($this->required as $field) {
            $required[] = static::normaliseFieldName($this->className, (string) $field);
        }

        $allowed = array_keys(DataObject::getSchema()->databaseFields($this->className));
        $fields = array_intersect($allowed, $required);
        $singleton = singleton($this->className);

        $new = [];
        foreach ($fields as $field) {
            $field = Convert::raw2sql($field);
            if (isset($data[$field])) {
                // allow wildcard in filter
                if ($data[$field] === '*') {
                    continue;
                }

                $dbfield = $singleton->dbObject($field);
                $value = $dbfield->prepValueForDB($data[$field]);

                $new[] = sprintf("\"%s\" = '%s'", $field, $value);
            } elseif ($singleton->dbObject($field) instanceof DBForeignKey) {
                // unset has_one relations are stored as 0
                $new[] = sprintf('("%s" = 0 OR "%s" IS NULL)', $field, $field);
            } else {
                $new[] = sprintf('"%s" IS NULL', $field);
            }
        }

        return $new;
    }
}

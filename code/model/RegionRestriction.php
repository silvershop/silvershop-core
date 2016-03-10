<?php

class RegionRestriction extends DataObject
{
    private static $db             = array(
        "Country"    => "ShopCountry",
        "State"      => "Varchar",
        "City"       => "Varchar",
        "PostalCode" => "Varchar(10)",
    );

    private static $defaults       = array(
        "Country"    => "*",
        "State"      => "*",
        "City"       => "*",
        "PostalCode" => "*",
    );

    private static $default_sort   = "\"Country\" ASC, \"State\" ASC, \"City\" ASC, \"PostalCode\" ASC";

    private static $summary_fields = array(
        'Country',
        'State',
        'City',
        'PostalCode',
    );

    private static $field_labels   = array(
        'Country'    => 'Country',
        'State'      => 'State/Region',
        'City'       => 'City/Sub-Region',
        'PostalCode' => 'Post/Zip Code',
    );

    /*
     * Specifies form field types to use in TableFields
     */
    private static $table_field_types = array(
        'Country'    => 'RestrictionRegionCountryDropdownField',
        'State'      => 'TextField',
        'City'       => 'TextField',
        'PostalCode' => 'TextField',
    );

    /**
     * Produce a SQL filter to get matching RegionRestrictions to a given address
     *
     * @param Address $address
     */
    public static function address_filter(Address $address)
    {
        $restrictables = array(
            "Country",
            "State",
            "City",
            "PostalCode",
        );
        $where = array();
        $rr = "\"RegionRestriction\".";
        foreach ($restrictables as $field) {
            $where[] = "TRIM(LOWER($rr\"$field\")) = TRIM(LOWER('" . Convert::raw2sql($address->$field)
                . "')) OR $rr\"$field\" = '*' OR $rr\"$field\" = ''";
        }
        return "(" . implode(") AND (", $where) . ")";
    }

    public static function get_table_field_types()
    {
        return self::$table_field_types;
    }

    /**
     * Produces a sort check to make wildcards come last.
     * Useful because we are only interested in the wildcard,
     * and not sorting of other values.
     */
    public static function wildcard_sort($field, $direction = "ASC")
    {
        return "CASE \"{$field}\" WHEN '*' THEN 1 ELSE 0 END $direction";
    }

    public function onBeforeWrite()
    {
        //prevent empty data - '*' must be used
        foreach (self::$defaults as $field => $value) {
            if (empty($this->$field)) {
                $this->$field = $value;
            }
        }
        //TODO: prevent non-heirarichal entries, eg country = '*', then state = 'blah'
        parent::onBeforeWrite();
    }
}

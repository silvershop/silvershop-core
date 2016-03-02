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
     * Parses a UK postcode to give you the different sections
     *
     * @param str $postcode
     * @return array
     */
    public static function parse_uk_postcode($postcode) {
        $postcode = str_replace(' ', '', $postcode); // remove any spaces;
        $postcode = strtoupper($postcode); // force to uppercase;
        $valid_postcode_exp = "/^(([A-PR-UW-Z]{1}[A-IK-Y]?)([0-9]?[A-HJKS-UW]?[ABEHMNPRVWXY]?|[0-9]?[0-9]?))\s?([0-9]{1}[ABD-HJLNP-UW-Z]{2})$/i";

        // set default output results (assuming invalid postcode):
        $output['validate'] = false;
        $output['prefix'] = '';
        $output['suffix'] = '';

        if (preg_match($valid_postcode_exp, strtoupper($postcode))) {
            $output['validate'] = true;
            $suffix = substr($postcode, -3);
            $prefix = str_replace($suffix, '', $postcode);
            $output['prefix'] = $prefix;
            $output['suffix'] = $suffix;
        }
        return $output;
    }

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
            "City"
        );
        $where = array();
        $rr = "\"RegionRestriction\".";
        foreach ($restrictables as $field) {
            $where[] = "TRIM(LOWER($rr\"$field\")) = TRIM(LOWER('" . Convert::raw2sql($address->$field)
                . "')) OR $rr\"$field\" = '*' OR $rr\"$field\" = ''";
        }

        // check that the country is set to the uk
        if($address->Country == 'GB') {
            // will check for partial postcodes (eg. NE, NE17, NE177AH)
            $postcode = self::parse_uk_postcode($address->PostalCode);
            if (isset($postcode['validate']) && $postcode['validate']) {
                $region = preg_replace("/[^a-z]+/i", "", substr($postcode['prefix'],0,2));
                $where[] = "TRIM(LOWER($rr\"PostalCode\")) = TRIM(LOWER('" . Convert::raw2sql($region)
                    . "')) OR TRIM(LOWER($rr\"PostalCode\")) = TRIM(LOWER('" . Convert::raw2sql($postcode['prefix'])
                    . "')) OR TRIM(LOWER($rr\"PostalCode\")) = TRIM(LOWER('" . Convert::raw2sql($postcode['prefix'].$postcode['suffix'])
                    . "')) OR $rr\"PostalCode\" = '*' OR $rr\"PostalCode\" = ''";
            }
            else {
                $where[] = "TRIM(LOWER($rr\"PostalCode\")) = TRIM(LOWER('" . Convert::raw2sql($address->PostalCode)
                    . "')) OR $rr\"PostalCode\" = '*' OR $rr\"PostalCode\" = ''";
            }
        }
        else {
            $where[] = "TRIM(LOWER($rr\"PostalCode\")) = TRIM(LOWER('" . Convert::raw2sql($address->PostalCode)
                . "')) OR $rr\"PostalCode\" = '*' OR $rr\"PostalCode\" = ''";
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

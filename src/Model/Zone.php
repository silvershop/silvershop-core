<?php

namespace SilverShop\Model;


use SilverShop\ShopTools;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;


/**
 * TODO: Move this to shipping module…
 * A zone is a collection of regions. Zones can cross over each other.
 * Zone matching is prioritised by specificity. For example, a matching post code
 * will take priority over a matching country.
 *
 * @property string $Name
 * @property string $Description
 * @method ZoneRegion[]|HasManyList Regions()
 */
class Zone extends DataObject
{
    private static $db = [
        'Name' => 'Varchar',
        'Description' => 'Varchar',
    ];

    private static $has_many = [
        'Regions' => ZoneRegion::class,
    ];

    private static $summary_fields = [
        'Name',
        'Description',
    ];

    private static $table_name = 'SilverShop_Zone';

    /*
     * Returns a DataSet of matching zones
    */
    public static function get_zones_for_address(Address $address)
    {
        $zones = ZoneRegion::filteredByAddress($address);
        $zoneIds = $zones->column('ZoneID');
        if (empty($zoneIds)) {
            return null;
        }
        return self::get()->byIDs($zoneIds);
    }

    /*
     * Get ids of zones, and store in session
     */
    public static function cache_zone_ids(Address $address)
    {
        $session = ShopTools::getSession();
        if ($zones = self::get_zones_for_address($address)) {
            $ids = $zones->map('ID', 'ID')->toArray();
            $session->set('MatchingZoneIDs', implode(',', $ids));
            return $ids;
        }
        $session->set('MatchingZoneIDs', null)->clear('MatchingZoneIDs');
        return null;
    }

    /**
     * Get cached ids as array
     */
    public static function get_zone_ids()
    {
        $session = ShopTools::getSession();
        if ($ids = $session->get('MatchingZoneIDs')) {
            return explode(',', $ids);
        }
        return null;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->fieldByName('Root')->removeByName('Regions');
        if ($this->isInDB()) {
            $regionsTable = GridField::create(
                'Regions',
                $this->fieldLabel('Regions'),
                $this->Regions(),
                GridFieldConfig_RelationEditor::create()
            );
            $fields->addFieldsToTab('Root.Main', $regionsTable);
        }
        return $fields;
    }
}



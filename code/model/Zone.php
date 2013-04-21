<?php

/*
 * A zone is a collection of regions. Zones can cross over each other.
 * Zone matching is prioritised by specificity. For example, a matching post code
 * will take priority over a matching country.
 */
class Zone extends DataObject{
	
	static $db = array(
		'Name' => 'Varchar',
		'Description' => 'Varchar'
	);
	
	static $has_many = array(
		'Regions' => 'ZoneRegion'
	);

	/*
	 * Returns a DataSet of matching zones
	*/
	static function get_zones_for_address(Address $address){
		$where = RegionRestriction::address_filter($address);
		return Zone::get()->where($where)
					->sort('PostalCode DESC, City DESC, State DESC, Country DESC')
					->innerJoin("ZoneRegion","\"Zone\".\"ID\" = \"ZoneRegion\".\"ZoneID\"")
					->innerJoin("RegionRestriction","\"ZoneRegion\".\"ID\" = \"RegionRestriction\".\"ID\"");
	}
	
	/*
	 * Get ids of zones, and store in session
	 */
	static function cache_zone_ids(Address $address){
		if($zones = self::get_zones_for_address($address)){
			$ids = $zones->map('ID','ID')->toArray();
			Session::set("MatchingZoneIDs",implode(",",$ids));
			return $ids;
		}
		Session::set("MatchingZoneIDs",null);
		Session::clear("MatchingZoneIDs");
		return null;
	}
	
	/**
	 * Get cached ids as array
	 */
	static function get_zone_ids(){
		if($ids = Session::get("MatchingZoneIDs")){
			return explode(",",$ids);
		}
		return null;
	}
	
	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->fieldByName("Root")->removeByName("Regions");
		if($this->isInDB()){
			$regionsTable = new GridField("Regions", "Regions", $this->Regions(), new GridFieldConfig_RelationEditor());
			$fields->addFieldsToTab("Root.Main", array(
				$regionsTable
			));
		}
		return $fields;
	}
	
}

class ZoneRegion extends RegionRestriction{
	
	static $has_one = array(
		'Zone' => 'Zone'
	);
	
}

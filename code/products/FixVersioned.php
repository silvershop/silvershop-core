<?php

/**
 * Temporary fix for Versioned class.
 */
class FixVersioned extends Versioned{
	
	static function get_version($class, $id, $version) {
		$oldMode = FixVersioned::get_reading_mode();
		Versioned::set_reading_mode('');
	
		$baseTable = ClassInfo::baseDataClass($class);
		$query = singleton($class)->buildVersionSQL("\"{$baseTable}\".\"RecordID\" = $id AND \"{$baseTable}\".\"Version\" = $version");
		$record = $query->execute()->record();
		$className = $record['ClassName'];
		if(!$className) {
			return false;
		}
	
		FixVersioned::set_reading_mode($oldMode);
		return new $className($record);
	}
	
	static function get_all_versions($class, $id, $version) {
		$baseTable = ClassInfo::baseDataClass($class);
		$query = singleton($class)->buildVersionSQL("\"{$baseTable}\".\"RecordID\" = $id AND \"{$baseTable}\".\"Version\" = $version");
		$record = $query->execute()->record();
		$className = $record['ClassName'];
		if(!$className) {
			return false;
		}
		return new $className($record);
	}
	
}
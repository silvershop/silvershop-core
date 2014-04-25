<?php

class ZoneTest extends SapphireTest{

	public static $fixture_file = array(
		'shop/tests/fixtures/Zones.yml',
		'shop/tests/fixtures/Addresses.yml'
	);

	public function testMatchingZones() {
		$this->assertZoneMatch($this->objFromFixture("Address", "wnz6012"), "TransTasman");
		$this->assertZoneMatch($this->objFromFixture("Address", "wnz6012"), "Local");
		$this->assertZoneMatch($this->objFromFixture("Address", "sau5024"), "TransTasman");
		$this->assertZoneMatch($this->objFromFixture("Address", "sau5024"), "Special");
		$this->assertZoneMatch($this->objFromFixture("Address", "scn266033"), "Asia");
		$this->assertNoZoneMatch($this->objFromFixture("Address", "zch1234"));
		
		$this->markTestIncomplete(
			'test match specificity, ie state matches should come before country matches, but not postcode matches'
		);
	}

	public function assertZoneMatch($address, $zonename) {
		$zones = Zone::get_zones_for_address($address);
		$this->assertNotNull($zones);
		$this->assertDOSContains(array(
			array('Name' => $zonename)
		), $zones);
	}

	public function assertNoZoneMatch($address) {
		$zones = Zone::get_zones_for_address($address);
		$this->assertFalse($zones->exists(), "No zones exist");
	}

}

<?php

class RegionRestrictionTest extends SapphireTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/RegionRestriction.yml',
		'shop/tests/fixtures/Addresses.yml'
	);
		
	function testMatchLocal(){
		$address = $this->objFromFixture("Address", "wnz6012");
		$rate = $this->getRate($address);
		$this->assertTrue((boolean)$rate);
		$this->assertEquals($rate->Rate,2);
	}
	
	function testMatchRegional(){
		$address = $this->objFromFixture("Address", "wnz6022");
		$rate = $this->getRate($address);
		$this->assertTrue((boolean)$rate);
		$this->assertEquals($rate->Rate,10);
	}
	
	function testMatchNational(){
		$address = $this->objFromFixture("Address", "anz1010");
		$rate = $this->getRate($address);
		$this->assertTrue((boolean)$rate);
		$this->assertEquals($rate->Rate,50);
	}
		
	function testMatchDefault(){
		//add default rate
		$default = new RegionRestriction_RateTest(array(
			'Rate' => 100
		));
		$default->write();
		$address = $this->objFromFixture("Address", "bukhp193eq");
		$rate = $this->getRate($address);
		$this->assertTrue((boolean)$rate);
		$this->assertEquals($rate->Rate,100);
	}
	
	function testNoMatch(){
		$address = $this->objFromFixture("Address", "bukhp193eq");
		$rate = $this->getRate($address);
		$this->assertFalse($rate);
	}
	
	function testMatchSQLEscaping(){
		$address = new Address(array(
			"Country" => "IT",
			"State" => "Valle d'Aosta"
		));
		$rate = $this->getRate($address);
		$this->assertFalse((boolean)$rate,"Can't find rate with unescaped data");
		
		$address = new Address(array(
			"Country" => "NZ",
			"State" => "Hawke's Bay"
		));
		$rate = $this->getRate($address);
		$this->assertTrue((boolean)$rate,"Rate with unescaped data found");
	}
	
	function getRate(Address $address){
		$where = RegionRestriction::address_filter($address);
		return DataObject::get_one("RegionRestriction_RateTest", $where, true, "Rate ASC");
	}
	
}

class RegionRestriction_RateTest extends RegionRestriction{
	
	static $db = array(
		'Rate' => 'Currency'
	);
	
}
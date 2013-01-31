<?php 

class ShopUserInfoTest extends SapphireTest{
	
	function testSetLocation(){
		
		ShopUserInfo::set_location(array(
			'Country' => 'NZ',
			'State' => 'Wellington',
			'City' => 'Newton',
			'Longitude' => 10.555,
			'Latitude' => -23.44
		));
		
		$location = ShopUserInfo::get_location();
		
		$this->assertEquals($location->Country,'NZ');
		$this->assertEquals($location->State,'Wellington');
		$this->assertEquals($location->City,'Newton');
		$this->assertEquals($location->Longitude, 10.555);
		$this->assertEquals($location->Latitude,-23.44);
	}
	
}
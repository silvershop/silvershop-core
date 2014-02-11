<?php

class ShopUserInfoTest extends SapphireTest{

	public function testSetLocation() {

		ShopUserInfo::set_location(array(
			'Country' => 'NZ',
			'State' => 'Wellington',
			'City' => 'Newton'
		));

		$location = ShopUserInfo::get_location();

		$this->assertEquals($location->Country, 'NZ');
		$this->assertEquals($location->State, 'Wellington');
		$this->assertEquals($location->City, 'Newton');
	}

}

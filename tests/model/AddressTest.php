<?php
class AddressTest extends SapphireTest{
	
	function testForm(){
		$address = new Address(array(
			'Country'		=> 'NZ',
			'State'			=> 'Wellington',
			'City'			=> 'TeAro',
			'PostalCode' 	=> '1333',
			'Address'		=> '23 Blah Street',
			'AddressLine2'	=> 'Fitzgerald Building, Foor 3',
			'Latitude'		=> '120',
			'Longitude'		=> '-42',
			'Company'		=> 'Ink inc',
			'FirstName'		=> 'Jerald',
			'Surname'		=> 'Smith',
			'Phone'			=> '12346678',
		));
		$fields = $address->getFrontEndFields();
		$requiremetns = $address->getRequiredFields();
		$this->assertEquals(
			"23 Blah Street|Fitzgerald Building, Foor 3|TeAro|Wellington|1333|NZ",
			$address->toString("|")
		);
	}
	
}
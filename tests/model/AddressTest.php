<?php
class AddressTest extends SapphireTest
{
	public function testForm(){
		$address = new Address(array(
			'Country'		=> 'NZ',
			'State'			=> 'Wellington',
			'City'			=> 'TeAro',
			'PostalCode' 	=> '1333',
			'Address'		=> '23 Blah Street',
			'AddressLine2'	=> 'Fitzgerald Building, Foor 3',
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

	public function testRequiredFields(){
		// create address instance that lacks some required fields (Address)
		$address = Address::create(array(
			'Country'		=> 'NZ',
			'State'			=> 'Wellington',
			'City'			=> 'TeAro'
		));

		$writeFailed = false;
		try {
			$address->write();
		} catch(Exception $ex){
			$writeFailed = true;
		}

		$this->assertTrue($writeFailed, "Address should not be writable, since it doesn't contain all required fields");


		// Create an Address that satisfies the baseline required fields, but not the ones that were added via subclass.
		$address = ExtendedTestAddress::create(array(
			'Country'		=> 'NZ',
			'State'			=> 'Wellington',
			'City'			=> 'TeAro',
			'Address'		=> '23 Blah Street'
		));

		$writeFailed = false;
		try {
			$address->write();
		} catch(Exception $ex){
			$writeFailed = true;
		}

		$this->assertTrue($writeFailed, "Address should not be writable, since it doesn't contain required fields added via subclass");
	}
}

class ExtendedTestAddress extends Address {
	// Addd postal code to required fields
	public function getRequiredFields() {
		$fields = parent::getRequiredFields();
		$fields['PostalCode'] = 'PostalCode';
		return $fields;
	}
}

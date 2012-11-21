<?php
class AddressTest extends SapphireTest{
	
	function testForm(){
		$address = new Address();
		$fields = $address->getFormFields();
		$requiremetns = $address->getRequiredFields();
		
		$address->getFieldMap("prefix");
		$address->toString("|");
		
		//TODO: assertions
		
	}
	
	function testFieldAliasSetters(){
		$address = new Address();
		$aliases = array(
			"Province" => "State",
			"Territory" => "State",
			"Island" => "State",
			"Suburb" => "City",
			"County" => "City",
			"District" => "City",
			"PostCode" => "PostalCode",
			"ZipCode" => "PostalCode",
			"Street" => "Address",
			"Street2" => "AddressLine2",
			"Address2" => "AddressLine2",
			"Institution" => "Company",
			"Business" => "Company",
			"Organisation" => "Company",
			"Organization" => "Company"
		);
		foreach($aliases as $alias => $field){
			$address->$alias = strtolower($alias);
			$this->assertEquals($address->$field,strtolower($alias));
		}
	}
	
}
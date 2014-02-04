<?php

class SortControlTest extends SapphireTest{


	public function testCreateSort(){

		$sorter = new SortControl("SortControlTest");

		//add sort
		$sorter->addSort("Popularity", "Most Popular", array(
			"Popularity" => "ASC",
			"Created" => "DESC"
		));
		$sorter->addSort("Newest", "Newest", array(
			"Creaated" => "DESC"
		));
		$sorter->addSort("LowPrice","Lowest Price",array(
			"Price" => "ASC"
		));

		$this->assertEquals($sorter->getSortOptions(),array(
			"Popularity" => "Most Popular",
			"Newest" => "Newest",
			"LowPrice" => "Lowest Price"
		),"Sort options are accurate");

		$this->assertFalse($sorter->validateSort("Age"),"Age is invalid");
		$this->assertTrue($sorter->validateSort("Newest"),"Newest is valid");

		$this->assertEquals($sorter->getSortName(),"Popularity","Default sort is 'Popularity'");
		$this->assertEquals($sorter->getSortSQL(),"Popularity ASC, Created DESC","Popularity SQL is created correctly");
		$sorter->setSort("X");
		$this->assertEquals($sorter->getSortName(),"Popularity","Sort has not been updated");
		$sorter->setSort("LowPrice");
		$this->assertEquals($sorter->getSortName(),"LowPrice","New sort has been set to 'LowPrice'");

		$sorter->removeSort("Newest");
		$this->assertEquals($sorter->getSortOptions(),array(
			"Popularity" => "Most Popular",
			"LowPrice" => "Lowest Price"
		),"Sort options are accurate");

		$sorter->clearAll();

		$this->assertEquals($sorter->getSortOptions(),array(),"Sort options are empty");

	}




}

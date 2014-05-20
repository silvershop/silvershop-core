<?php

class ShopReportTest extends SapphireTest{
	
	function testSalesReport() {
		$report = new ShopSalesReport();
		$records = $report->sourceRecords(array('Grouping' => 'Year'));
		$records = $report->sourceRecords(array('Grouping' => 'Month'));
		$records = $report->sourceRecords(array('Grouping' => 'Week'));
		$records = $report->sourceRecords(array('Grouping' => 'Day'));
	}

}
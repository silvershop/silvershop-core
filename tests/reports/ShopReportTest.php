<?php

class ShopReportTest extends SapphireTest{
	
	function testSalesReport() {
		$report = new ShopSalesReport();
		$records = $report->sourceRecords(array('Grouping' => 'Year'));
		$records = $report->sourceRecords(array('Grouping' => 'Month'));
		$records = $report->sourceRecords(array('Grouping' => 'Week'));
		$records = $report->sourceRecords(array('Grouping' => 'Day'));
		$records = $report->sourceRecords(array(
			'Grouping' => 'Day',
			'StartPeriod' => 'May 1, 2010',
			'EndPeriod' => 'May 16, 2111'
		));
	}

}
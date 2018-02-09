<?php

namespace SilverShop\Tests\Reports;

use SilverShop\Reports\ShopSalesReport;
use SilverStripe\Dev\SapphireTest;

class ShopReportTest extends SapphireTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    function testSalesReport()
    {
        $report = new ShopSalesReport();
        $records = $report->sourceRecords(array());
        $records = $report->sourceRecords(array('Grouping' => 'Year'));
        $records = $report->sourceRecords(array('Grouping' => 'Month'));
        $records = $report->sourceRecords(array('Grouping' => 'Week'));
        $records = $report->sourceRecords(
            array(
                'Grouping'    => 'Day',
                'StartPeriod' => 'May 1, 2010',
                'EndPeriod'   => 'May 16, 2111',
            )
        );
        $record = $records->first();
        $this->assertEquals("02 October 2012 - Tuesday", $record->FilterPeriod);
        $this->assertEquals(1, $record->Count, "One sale on this day");
    }
}

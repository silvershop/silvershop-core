<?php
/**
 * Shows a chart of recent orders
 *
 * @date       09.24.2014
 * @package    shop
 * @subpackage dashboard
 */
if (class_exists('DashboardPanel')) {
    class DashboardRecentOrdersChart extends DashboardPanel
    {
        private static $db = array(
            'Days' => 'Int',
        );

        public function getLabel()
        {
            return _t('ShopDashboard.RecentOrdersChart', 'Order History Chart');
        }

        public function getDescription()
        {
            return _t('ShopDashboard.RecentOrdersChartDescription', 'Shows recent orders on a graph');
        }

        public function getConfiguration()
        {
            $fields = parent::getConfiguration();
            $fields->push(TextField::create("Days", _t('ShopDashboard.NumberOfDays', "Number of days to show")));
            return $fields;
        }

        public function Chart()
        {
            if ($this->Days == 0) {
                $this->Days = 30;
            }
            $chart = DashboardChart::create("Order History, last {$this->Days} days", "Date", "Number of orders");

            $result = DB::query(
                "
				SELECT COUNT(*) AS \"OrderCount\", DATE_FORMAT(\"Placed\",'%d %b %Y') AS \"Date\"
				FROM \"Order\"
				WHERE \"Status\" not in ('" . implode(
                    "','",
                    Config::inst()
                        ->get('DashboardRecentOrdersPanel', 'exclude_status')
                ) . "')
					AND \"Placed\" > date_sub(now(), interval {$this->Days} day)
				GROUP BY \"Date\", \"Placed\"
				ORDER BY \"Placed\"
			"
            );

            if ($result) {
                while ($row = $result->nextRecord()) {
                    $chart->addData($row['Date'], $row['OrderCount']);
                }
            }
            return $chart;
        }
    }
}

<?php
/**
 * Shows a table of recent orders
 *
 * @date       09.24.2014
 * @package    shop
 * @subpackage dashboard
 */
if (class_exists('DashboardPanel')) {
    class DashboardRecentOrdersPanel extends DashboardPanel
    {
        private static $exclude_status = array('Cart');

        private static $db             = array(
            'Count' => 'Int',
        );

        public function getLabel()
        {
            return _t('ShopDashboard.RECENTORDERS', 'Recent Orders');
        }

        public function getDescription()
        {
            return _t('ShopDashboard.RECENTORDERSDESCRIPTION', 'Shows recent orders');
        }

        public function getConfiguration()
        {
            $fields = parent::getConfiguration();
            $fields->push(TextField::create("Count", _t('ShopDashboard.NUMBER_OF_ORDERS', "Number of orders to show")));
            return $fields;
        }

        public function Orders()
        {
            if ($this->Count == 0) {
                $this->Count = 10;
            }
            $orders = Order::get()
                ->sort("Placed", "DESC")
                ->exclude('Status', Config::inst()->get('DashboardRecentOrdersPanel', 'exclude_status'))
                ->limit($this->Count);
            return $orders;
        }
    }
}

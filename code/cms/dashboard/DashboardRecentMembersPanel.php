<?php
/**
 * Shows a table of recent signups
 *
 * @date       09.24.2014
 * @package    shop
 * @subpackage dashboard
 */
if (class_exists('DashboardPanel')) {
    class DashboardRecentMembersPanel extends DashboardPanel
    {
        private static $exclude_status = array('Cart');

        private static $db             = array(
            'Count' => 'Int',
        );

        public function getLabel()
        {
            return _t('ShopDashboard.RecentAccounts', 'Recent Accounts');
        }

        public function getDescription()
        {
            return _t('ShopDashboard.RecentAccountsDescription', 'Shows recent account signups');
        }

        public function getConfiguration()
        {
            $fields = parent::getConfiguration();
            $fields->push(
                TextField::create("Count", _t('ShopDashboard.NUMBER_OF_ACCOUNTS', "Number of accounts to show"))
            );
            return $fields;
        }

        public function Members()
        {
            if ($this->Count == 0) {
                $this->Count = 10;
            }
            $orders = Member::get()
                ->sort("Created", "DESC")
                ->limit($this->Count);
            return $orders;
        }
    }
}

<?php

namespace SilverShop\Page;

use Page;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;

/**
 * Account page shows order history and a form to allow
 * the member to edit his/her details.
 *
 * @package shop
 */
class AccountPage extends Page
{
    private static $icon = 'silvershop/core: client/dist/images/icons/account.gif';

    private static $table_name = 'SilverShop_AccountPage';

    public function canCreate($member = null, $context = array())
    {
        return !self::get()->exists();
    }

    /**
     * Returns the link or the URLSegment to the account page on this site
     *
     * @param boolean $urlSegment Return the URLSegment only
     *
     * @return mixed
     */
    public static function find_link($urlSegment = false)
    {
        $page = self::get_if_account_page_exists();
        return ($urlSegment) ? $page->URLSegment : $page->Link();
    }

    /**
     * Return a link to view the order on the account page.
     *
     * @param int|string $orderID    ID of the order
     * @param boolean    $urlSegment Return the URLSegment only
     *
     * @return string
     */
    public static function get_order_link($orderID, $urlSegment = false)
    {
        $page = self::get_if_account_page_exists();
        return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . 'order/' . $orderID;
    }

    /**
     * @return AccountPage
     */
    protected static function get_if_account_page_exists()
    {
        if ($page = DataObject::get_one(self::class)) {
            return $page;
        }
        user_error(_t(__CLASS__ . '.NoPage', 'No AccountPage was found. Please create one in the CMS!'), E_USER_ERROR);
        return null; // just to keep static analysis happy
    }

    /**
     * This module always requires a page model.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if (!self::get()->exists() && $this->config()->create_default_pages) {
            /**
             * @var AccountPage $page
             */
            $page = self::create()->update(
                [
                'Title' => 'Account',
                'URLSegment' => AccountPageController::config()->url_segment,
                'ShowInMenus' => 0,
                ]
            );
            $page->write();
            $page->publishSingle();
            $page->flushCache();
            DB::alteration_message('Account page created', 'created');
        }
    }
}

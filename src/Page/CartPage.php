<?php

namespace SilverShop\Page;

use Page;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DB;

/**
 * View and edit the cart in a full page.
 * Visitor can continue shopping, or proceed to checkout.
 * @property int $CheckoutPageID
 * @property int $ContinuePageID
 * @method CheckoutPage CheckoutPage()
 * @method SiteTree ContinuePage()
 */
class CartPage extends Page
{
    private static array $has_one = [
        'CheckoutPage' => CheckoutPage::class,
        'ContinuePage' => SiteTree::class,
    ];

    private static string $icon = 'silvershop/core: client/dist/images/icons/cart.gif';

    private static string $table_name = 'SilverShop_CartPage';

    /**
     * Returns the link to the checkout page on this site
     *
     * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
     *
     * @return string Link to checkout page
     */
    public static function find_link($urlSegment = false, $action = false, $id = false): string
    {
        $base = CartPageController::config()->url_segment;
        if ($page = self::get()->first()) {
            $base = $page->Link();
        }
        return Controller::join_links($base, $action, $id);
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(
            function (FieldList $fieldList): void {
                if ($checkouts = CheckoutPage::get()) {
                    $fieldList->addFieldToTab(
                        'Root.Links',
                        DropdownField::create(
                            'CheckoutPageID',
                            $this->fieldLabel('CheckoutPage'),
                            $checkouts->map("ID", "Title")
                        )
                    );
                }

                $fieldList->addFieldToTab(
                    'Root.Links',
                    TreeDropdownField::create(
                        'ContinuePageID',
                        $this->fieldLabel('ContinuePage'),
                        SiteTree::class
                    )
                );
            }
        );

        return parent::getCMSFields();
    }

    /**
     * This module always requires a page model.
     */
    public function requireDefaultRecords(): void
    {
        parent::requireDefaultRecords();
        if (!self::get()->exists() && $this->config()->create_default_pages) {
            $page = self::create()->update(
                [
                    'Title' => 'Shopping Cart',
                    'URLSegment' => CartPageController::config()->url_segment,
                    'ShowInMenus' => 0,
                ]
            );
            $page->write();
            $page->publishSingle();
            $page->flushCache();
            DB::alteration_message('Cart page created', 'created');
        }
    }
}

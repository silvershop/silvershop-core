<?php

namespace SilverShop\Page;

use Page;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\DB;

/**
 * CheckoutPage is a CMS page-type that shows the order
 * details to the customer for their current shopping
 * cart on the site.
 *
 * @see \SilverShop\Page\CheckoutPageController->Order()
 *
 * @package shop
 * @property ?string $PurchaseComplete
 */
class CheckoutPage extends Page
{
    private static array $db   = [
        'PurchaseComplete' => 'HTMLText',
    ];

    private static string $icon = 'silvershop/core: client/dist/images/icons/money.gif';

    private static string $table_name = 'SilverShop_CheckoutPage';

    /**
     * @config
     * @var array
     */
    private static $steps;

    /**
     * Returns the link to the checkout page on this site
     *
     * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
     */
    public static function find_link($urlSegment = false, $action = null, $id = null): string
    {
        $base = CheckoutPageController::config()->url_segment;
        if ($page = self::get()->first()) {
            $base = $page->Link();
        }
        return Controller::join_links($base, $action, $id);
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(
            function (FieldList $fieldList): void {
                $fieldList->addFieldsToTab(
                    'Root.Main',
                    [
                    HtmlEditorField::create(
                        'PurchaseComplete',
                        $this->fieldLabel('PurchaseComplete'),
                        4
                    )
                        ->setDescription(
                            _t(
                                __CLASS__ . '.PurchaseCompleteDescription',
                                "This message is included in reciept email, after the customer submits the checkout"
                            )
                        ),
                    ],
                    'Metadata'
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
                    'Title'       => 'Checkout',
                    'URLSegment'  => CheckoutPageController::config()->url_segment,
                    'ShowInMenus' => 0,
                ]
            );
            $page->write();
            $page->publishSingle();
            $page->flushCache();
            DB::alteration_message('Checkout page created', 'created');
        }
    }
}

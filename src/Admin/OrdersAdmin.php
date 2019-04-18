<?php

namespace SilverShop\Admin;

use SilverShop\Forms\GridField\OrderGridFieldDetailForm_ItemRequest;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\ORM\DataObject;

/**
 * Order administration interface, based on ModelAdmin
 *
 * @package SilverShop\Admin
 */
class OrdersAdmin extends ModelAdmin
{
    private static $url_segment = 'orders';

    private static $menu_title = 'Orders';

    private static $menu_priority = 1;

    private static $menu_icon_class = 'silvershop-icon-cart';

    private static $managed_models = [
        Order::class,
        OrderStatusLog::class
    ];

    private static $model_importers = array();

    /**
     * Restrict list to non-hidden statuses
     */
    public function getList()
    {
        $list = parent::getList();

        if ($this->modelClass == Order::class) {
            // Exclude hidden statuses
            $list = $list->exclude('Status', Order::config()->hidden_status); 
            $this->extend('updateList', $list);
        }

        return $list;
    }

    /**
     * Replace gridfield detail form to include print functionality
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        if ($this->modelClass == Order::class) {
            /** @var GridFieldConfig $config */
            $config = $form
                ->Fields()
                ->fieldByName($this->sanitiseClassName($this->modelClass))
                ->getConfig();

            $config
                ->getComponentByType(GridFieldSortableHeader::class)
                ->setFieldSorting([ 'StatusI18N' => 'Status' ]);

            $config
                ->getComponentByType(GridFieldDetailForm::class)
                ->setItemRequestClass(OrderGridFieldDetailForm_ItemRequest::class); //see below
        }

        if ($this->modelClass == OrderStatusLog::class) {
            /** @var GridFieldConfig $config */
            $config = $form
                ->Fields()
                ->fieldByName($this->sanitiseClassName($this->modelClass))
                ->getConfig();

            // Remove add new button
            $config->removeComponentsByType($config->getComponentByType(GridFieldAddNewButton::class));
        }

        return $form;
    }
}

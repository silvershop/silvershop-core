<?php

namespace SilverShop\Admin;

use SilverShop\Forms\GridField\OrderGridFieldDetailForm_ItemRequest;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;

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
        if ($this->modelClass == $this->sanitiseClassName(Order::class)) {
            $context = $this->getSearchContext();
            $params = $this->request->requestVar('q');
            //TODO update params DateTo, to include the day, ie 23:59:59
            $list = $context->getResults($params)
                ->exclude('Status', Order::config()->hidden_status); //exclude hidden statuses

            $this->extend('updateList', $list);
            return $list;
        } else {
            return parent::getList();
        }
    }

    /**
     * Replace gridfield detail form to include print functionality
     */
    function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        if ($this->modelClass == $this->sanitiseClassName(Order::class)) {
            /** @var GridFieldConfig $config */
            $config = $form->Fields()->fieldByName($this->modelClass)->getConfig();
            $config->getComponentByType(GridFieldSortableHeader::class)->setFieldSorting(
                [
                    'StatusI18N' => 'Status'
                ]
            );
            $config
                ->getComponentByType(GridFieldDetailForm::class)
                ->setItemRequestClass(OrderGridFieldDetailForm_ItemRequest::class); //see below
        }
        if ($this->modelClass == $this->sanitiseClassName(OrderStatusLog::class)) {
            /** @var GridFieldConfig $config */
            $config = $form->Fields()->fieldByName($this->modelClass)->getConfig();
            // Remove add new button
            $config->removeComponentsByType($config->getComponentByType(GridFieldAddNewButton::class));
        }

        return $form;
    }
}

<?php

namespace SilverShop\Core\Cms;


use SilverShop\Core\Model\Order;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDetailForm;


/**
 * Order administration interface, based on ModelAdmin
 *
 * @package    shop
 * @subpackage cms
 */
class OrdersAdmin extends ModelAdmin
{
    private static $url_segment    = 'orders';

    private static $menu_title     = 'Orders';

    private static $menu_priority  = 1;

    private static $menu_icon      = 'silvershop/images/icons/order-admin.png';

    private static $managed_models = array(
        'Order' => array(
            'title' => 'Orders',
        ),
        'OrderStatusLog'
    );

    private static $model_importers = array();

    /**
     * Restrict list to non-hidden statuses
     */
    public function getList()
    {
        if ($this->modelClass == "Order") {
            $context = $this->getSearchContext();
            $params = $this->request->requestVar('q');
            //TODO update params DateTo, to include the day, ie 23:59:59
            $list = $context->getResults($params)
                ->exclude("Status", Order::config()->hidden_status); //exclude hidden statuses

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
        if ($this->modelClass == "Order") {
            $form->Fields()->fieldByName("Order")->getConfig()
                ->getComponentByType(GridFieldDetailForm::class)
                ->setItemRequestClass('OrderGridFieldDetailForm_ItemRequest'); //see below
        }
        if ($this->modelClass == "OrderStatusLog") {
            // Remove add new button
            $config = $form->Fields()->fieldByName("OrderStatusLog")->getConfig();
            $config->removeComponentsByType($config->getComponentByType(GridFieldAddNewButton::class));
        }

        return $form;
    }

    /**
     * Ensure that SearchForm selection remains populated.
     */
    public function SearchForm()
    {
        $form = parent::SearchForm();
        $query = $this->request->getVar('q');
        if ($query && isset($query['Status'])) {
            $form->loadDataFrom(
                array(
                    'q' => array(
                        'Status' => implode(',', $query['Status']),
                    ),
                )
            );
        }

        return $form;
    }
}

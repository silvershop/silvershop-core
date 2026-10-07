<?php

declare(strict_types=1);

namespace SilverShop\Admin;

use SilverShop\Forms\GridField\OrderGridFieldDetailForm_ItemRequest;
use SilverShop\Model\Order;
use SilverShop\Model\OrderStatusLog;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\ORM\DataList;

/**
 * Order administration interface, based on ModelAdmin
 *
 * @package SilverShop\Admin
 */
class OrdersAdmin extends ModelAdmin
{
    private static string $url_segment = 'orders';

    private static string $menu_title = 'Orders';

    private static int $menu_priority = 1;

    private static string $menu_icon_class = 'silvershop-icon-cart';

    private static array $managed_models = [
        Order::class,
        OrderStatusLog::class
    ];

    private static array $model_importers = [];

    /**
     * Restrict list to non-hidden statuses
     */
    public function getList(): DataList
    {
        $list = parent::getList();

        if ($this->modelClass == Order::class) {
            // Exclude hidden statuses
            $list = $list->exclude(['Status' => Order::config()->get('hidden_status')]);
            $this->extend('updateList', $list);
        }

        return $list;
    }

    /**
     * Replace gridfield detail form to include print functionality
     */
    public function getEditForm($id = null, $fields = null): Form
    {
        $form = parent::getEditForm($id, $fields);
        if ($this->modelClass == Order::class) {
            $gridField = $form
                ->Fields()
                ->fieldByName($this->sanitiseClassName($this->modelClass));
            /** @var GridFieldConfig $config */
            $config = $gridField->getConfig();

            $config
                ->getComponentByType(GridFieldSortableHeader::class)
                ->setFieldSorting([ 'StatusI18N' => 'Status' ]);

            $config
                ->getComponentByType(GridFieldDetailForm::class)
                ->setItemRequestClass(OrderGridFieldDetailForm_ItemRequest::class); //see below

            // Show the Total column as formatted currency in the CMS list (e.g. "€1,234.56"), but keep the
            // CSV/print exports numeric — a formatted string would break anything parsing the export, so
            // existing integrations see no change.
            if ($dataColumns = $config->getComponentByType(GridFieldDataColumns::class)) {
                $dataColumns->setFieldCasting(['Total' => 'Currency->Nice']);

                $rawTotal = fn ($value) => $value;
                if ($export = $config->getComponentByType(GridFieldExportButton::class)) {
                    $columns = $dataColumns->getDisplayFields($gridField);
                    $columns['Total'] = $rawTotal;
                    $export->setExportColumns($columns);
                }
                if ($print = $config->getComponentByType(GridFieldPrintButton::class)) {
                    $columns = $dataColumns->getDisplayFields($gridField);
                    $columns['Total'] = $rawTotal;
                    $print->setPrintColumns($columns);
                }
            }
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

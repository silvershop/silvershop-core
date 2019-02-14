<?php

namespace SilverShop\Forms\GridField;

use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\LiteralField;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;

class OrderGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = [
        'edit',
        'view',
        'ItemEditForm',
        'printorder',
    ];

    /**
     * Add print button to order detail form
     */
    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $printlink = $this->Link('printorder') . '?print=1';
        $printwindowjs = <<<JS
            window.open('$printlink', 'print_order', 'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50');return false;
JS;
        $form->Actions()->push(
            LiteralField::create(
                'PrintOrder',
                "<button class=\"no-ajax grid-print-button btn action btn-primary font-icon-print\" onclick=\"javascript:$printwindowjs\">"
                . _t('SilverShop\Model\Order.Print', 'Print') . '</button>'
            )
        );

        return $form;
    }

    /**
     * Render order for printing
     */
    public function printorder()
    {
        Requirements::clear();
        //include print javascript, if print argument is provided
        if (isset($_REQUEST['print']) && $_REQUEST['print']) {
            Requirements::customScript('if(document.location.href.indexOf(\'print=1\') > 0) {window.print();}');
        }
        $title = _t('SilverShop\Model\Order.Invoice', 'Invoice');
        if ($id = $this->popupController->getRequest()->param('ID')) {
            $title .= " #$id";
        }

        return $this->record->customise(
            [
                'SiteConfig' => SiteConfig::current_site_config(),
                'Title'      => $title,
            ]
        )->renderWith('SilverShop\Admin\OrderAdmin_Printable');
    }
}

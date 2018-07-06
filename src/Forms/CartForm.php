<?php

namespace SilverShop\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\ShopTools;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\NumericField;

/**
 * Renders the cart inside a form, so that it is editable.
 *
 * @package shop
 */
class CartForm extends Form
{
    protected $cart;

    public function __construct($controller, $name = 'CartForm', $cart = null, $template = 'SilverShop\Cart\Cart')
    {
        $this->cart = $cart;
        $fields = FieldList::create(
            CartEditField::create('Items', '', $this->cart)
                ->setTemplate($template)
        );
        $actions = FieldList::create(
            FormAction::create('updatecart', _t(__CLASS__ . '.UpdateCart', 'Update Cart'))
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );

        parent::__construct($controller, $name, $fields, $actions);
    }

    /**
     * Update the cart using data collected
     */
    public function updatecart($data, $form)
    {
        $items = $this->cart->Items();
        $updatecount = $removecount = 0;

        $request = $this->getRequest();
        $order = ShoppingCart::curr();
        if ($request && $request->isAjax() && $order) {
            ShopTools::install_locale($order->Locale);
        }

        $numericConverter = NumericField::create('_temp');

        $messages = [];
        $badMessages = [];
        if (isset($data['Items']) && is_array($data['Items'])) {
            foreach ($data['Items'] as $itemid => $fields) {
                $item = $items->byID($itemid);
                if (!$item) {
                    continue;
                }
                //delete lines
                if (isset($fields['Remove']) || (isset($fields['Quantity']) && (int)$fields['Quantity'] <= 0)) {
                    if (ShoppingCart::singleton()->removeOrderItem($item)) {
                        $removecount++;
                    } else {
                        $badMessages[] = ShoppingCart::singleton()->getMessage();
                    }

                    continue;
                }
                //update quantities
                if (isset($fields['Quantity']) && $quantity = Convert::raw2sql($fields['Quantity'])) {
                    $numericConverter->setValue($quantity);
                    if (!ShoppingCart::singleton()->updateOrderItemQuantity($item, $numericConverter->dataValue())) {
                        $badMessages[] = ShoppingCart::singleton()->getMessage();
                    }
                }
                //update variations
                if (isset($fields['ProductVariationID']) && $id = Convert::raw2sql($fields['ProductVariationID'])) {
                    if ($item->ProductVariationID != $id) {
                        $item->ProductVariationID = $id;
                    }
                }
                //TODO: make updates through ShoppingCart class
                //TODO: combine with items that now match exactly
                //TODO: validate changes
                if ($item->isChanged()) {
                    $item->write();
                    $updatecount++;
                }
            }
        }
        if ($removecount) {
            $messages['remove'] = _t(
                __CLASS__ . '.REMOVED_ITEMS',
                'Removed {count} items.',
                'count is the amount that was removed',
                array('count' => $removecount)
            );
        }
        if ($updatecount) {
            $messages['updatecount'] = _t(
                __CLASS__ . '.UPDATED_ITEMS',
                'Updated {count} items.',
                'count is the amount that was updated',
                array('count' => $updatecount)
            );
        }
        if (count($messages)) {
            $form->sessionMessage(implode(' ', $messages), 'good');
        }

        if (count($badMessages)) {
            $form->sessionMessage(implode(' ', $badMessages), 'bad');
        }

        $this->extend('updateCartFormResponse', $request, $response, $form);

        return $response ? $response : $this->controller->redirectBack();
    }
}

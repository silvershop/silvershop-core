<?php

declare(strict_types=1);

namespace SilverShop\Forms;

use SilverShop\Model\Order;
use SilverShop\Model\Variation\Variation;
use SilverStripe\Control\RequestHandler;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\ShopTools;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Versioned\Versioned;

/**
 * Renders the cart inside a form, so that it is editable.
 *
 * @package shop
 */
class CartForm extends Form
{
    protected $cart;

    public function __construct(RequestHandler $requestHandler, $name = 'CartForm', $cart = null, $template = 'SilverShop\Cart\Cart')
    {
        $this->cart = $cart;
        $fieldList = FieldList::create(
            CartEditField::create('Items', '', $this->cart)
                ->setTemplate($template)
        );
        $actions = FieldList::create(
            FormAction::create('updatecart', _t(__CLASS__ . '.UpdateCart', 'Update Cart'))
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );

        parent::__construct($requestHandler, $name, $fieldList, $actions);
    }

    /**
     * Update the cart using data collected
     */
    public function updatecart(array $data, $form): HTTPResponse
    {
        $items = $this->cart->Items();
        $updatecount = 0;
        $removecount = 0;

        $request = $this->getRequest();
        $response = null;
        $order = ShoppingCart::curr();
        if ($request && $request->isAjax() && $order instanceof Order) {
            ShopTools::install_locale($order->Locale);
        }

        $messages = [];
        $badMessages = [];
        if (isset($data['Items']) && is_array($data['Items'])) {
            foreach ($data['Items'] as $itemid => $fields) {
                $item = $items->byID($itemid);
                if (!$item) {
                    continue;
                }

                //delete lines
                if (isset($fields['Remove']) || (isset($fields['Quantity']) && (string) $fields['Quantity'] !== '' && is_numeric(
                    $fields['Quantity']
                ) && (int) $fields['Quantity'] <= 0)) {
                    if (ShoppingCart::singleton()->removeOrderItem($item)) {
                        ++$removecount;
                    } else {
                        $badMessages[] = ShoppingCart::singleton()->getMessage();
                    }

                    continue;
                }

                //update quantities
                if (array_key_exists('Quantity', $fields)) {
                    $rawQty = $fields['Quantity'];
                    if ($rawQty !== null && $rawQty !== '') {
                        if (!is_numeric($rawQty)) {
                            $badMessages[] = _t(
                                __CLASS__ . '.INVALID_QUANTITY',
                                'Please enter a valid quantity.'
                            );
                        } else {
                            $qtyInt = (int) $rawQty;
                            if ($qtyInt < 0) {
                                $badMessages[] = _t(
                                    __CLASS__ . '.INVALID_QUANTITY',
                                    'Please enter a valid quantity.'
                                );
                            } elseif (!ShoppingCart::singleton()->updateOrderItemQuantity($item, $qtyInt)) {
                                $badMessages[] = ShoppingCart::singleton()->getMessage();
                            }
                        }
                    }
                }

                if (array_key_exists('ProductVariationID', $fields)) {
                    $id = (int) $fields['ProductVariationID'];
                    if ($id > 0 && (int) $item->ProductVariationID !== $id) {
                        $variation = Variation::has_extension(Versioned::class)
                            ? Versioned::get_by_stage(Variation::class, 'Live')->byID($id)
                            : Variation::get()->byID($id);
                        if (!$variation instanceof Variation || (int) $variation->ProductID !== (int) $item->ProductID) {
                            $badMessages[] = _t(__CLASS__ . '.INVALID_VARIATION', 'That variation is not valid for this product.');
                        } elseif (!ShoppingCart::singleton()->switchOrderItemVariation($item, $variation)) {
                            $badMessages[] = ShoppingCart::singleton()->getMessage();
                        }
                    }
                }

                if (isset($fields['Comment'])) {
                    $item->Comment = trim(strip_tags((string)$fields['Comment']));
                }

                if ($item->isChanged() || isset($fields['Comment'])) {
                    $item->write();
                    ++$updatecount;
                }
            }
        }

        if ($removecount !== 0) {
            $messages['remove'] = _t(
                __CLASS__ . '.REMOVED_ITEMS',
                'Removed {count} items.',
                'count is the amount that was removed',
                ['count' => $removecount]
            );
        }

        if ($updatecount !== 0) {
            $messages['updatecount'] = _t(
                __CLASS__ . '.UPDATED_ITEMS',
                'Updated {count} items.',
                'count is the amount that was updated',
                ['count' => $updatecount]
            );
        }

        if ($messages !== []) {
            $form->sessionMessage(implode(' ', $messages), 'good');
        }

        if ($badMessages !== []) {
            $form->sessionMessage(implode(' ', $badMessages), 'bad');
        }

        $this->extend('updateCartFormResponse', $request, $response, $form, $removecount, $updatecount);

        return $response ? $response : $this->controller->redirectBack();
    }
}

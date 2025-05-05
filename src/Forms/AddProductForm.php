<?php

namespace SilverShop\Forms;

use SilverStripe\Control\RequestHandler;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Cart\ShoppingCartController;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Buyable;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\RequiredFields;

/**
 * @package shop
 */
class AddProductForm extends Form
{
    /**
     * Populates quantity dropdown with this many values
     */
    protected int $maxquantity = 0;

    /**
     * Fields that can be saved to an order item.
     */
    protected array $saveablefields = [];

    public function __construct(RequestHandler $requestHandler, string $name = "AddProductForm")
    {

        parent::__construct(
            $requestHandler,
            $name,
            $this->getFormFields($requestHandler),
            $this->getFormActions(),
            $this->getFormValidator()
        );

        $this->addExtraClass("addproductform");

        $this->extend('updateAddProductForm');
    }

    /**
     * Choose maximum value to populate quantity dropdown
     */
    public function setMaximumQuantity($qty): static
    {
        $this->maxquantity = (int)$qty;

        return $this;
    }

    public function setSaveableFields(array $fields): void
    {
        $this->saveablefields = $fields;
    }

    public function addtocart($data, $form)
    {
        if ($buyable = $this->getBuyable($data)) {
            $cart = ShoppingCart::singleton();
            $request = $this->getRequest();

            $order = $cart->current();
            if ($request && $request->isAjax() && $order) {
                ShopTools::install_locale($order->Locale);
            }

            $saveabledata = ($this->saveablefields !== []) ? Convert::raw2sql(
                array_intersect_key($data, array_combine($this->saveablefields, $this->saveablefields))
            ) : $data;
            $quantity = isset($data['Quantity']) ? (int)$data['Quantity'] : 1;
            if (($this->maxquantity >0) && ($quantity > $this->maxquantity)) {
                $quantity = $this->maxquantity;
                $form->sessionMessage(
                    _t('SilverShop\Forms\AddProductForm.QuantitySetToMaximum', 'Set to maximum quantity'),
                    'good'
                );
            }
            $cart->add($buyable, $quantity, $saveabledata);
            if (!ShoppingCartController::config()->direct_to_cart_page) {
                $form->SessionMessage($cart->getMessage(), $cart->getMessageType());
            }

            $this->extend('updateAddToCart', $form, $buyable);

            $this->extend('updateAddProductFormResponse', $request, $response, $buyable, $quantity, $form);

            return $response ? $response : ShoppingCartController::direct($cart->getMessageType());
        }
    }

    public function getBuyable($data = null)
    {
        if ($this->controller->dataRecord instanceof Buyable) {
            return $this->controller->dataRecord;
        }
        return Product::get()->byID((int)$this->getRequest()->postVar('BuyableID'));
    }

    /**
     * @param RequestHandler $controller the controller instance that is being passed to the form
     */
    protected function getFormFields(RequestHandler $controller = null): FieldList
    {
        $fieldList = FieldList::create();

        if ($this->maxquantity !== 0) {
            $values = [];
            $count = 1;

            while ($count <= $this->maxquantity) {
                $values[$count] = $count;
                $count++;
            }

            $fieldList->push(DropdownField::create('Quantity', _t('SilverShop\Generic.Quantity', 'Quantity'), $values, 1));
        } else {
            $fieldList->push(
                NumericField::create('Quantity', _t('SilverShop\Generic.Quantity', 'Quantity'), 1)
                    ->setAttribute('type', 'number')
                    ->setAttribute('min', '0')
            );
        }

        return $fieldList;
    }

    protected function getFormActions(): FieldList
    {
        return FieldList::create(
            FormAction::create('addtocart', _t("SilverShop\Page\Product.AddToCart", 'Add to Cart'))
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );
    }

    protected function getFormValidator()
    {
        return RequiredFields::create(
            [
                'Quantity',
            ]
        );
    }
}

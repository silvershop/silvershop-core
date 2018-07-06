<?php

namespace SilverShop\Forms;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Cart\ShoppingCartController;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Buyable;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\DataObject;

/**
 * @package shop
 */
class AddProductForm extends Form
{
    /**
     * Populates quantity dropdown with this many values
     *
     * @var int
     */
    protected $maxquantity = 0;

    /**
     * Fields that can be saved to an order item.
     *
     * @var array
     */
    protected $saveablefields = array();

    public function __construct($controller, $name = "AddProductForm")
    {

        parent::__construct(
            $controller,
            $name,
            $this->getFormFields($controller),
            $this->getFormActions(),
            $this->getFormValidator()
        );

        $this->addExtraClass("addproductform");

        $this->extend('updateAddProductForm');
    }

    /**
     * Choose maximum value to populate quantity dropdown
     */
    public function setMaximumQuantity($qty)
    {
        $this->maxquantity = (int)$qty;

        return $this;
    }

    public function setSaveableFields($fields)
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

            $saveabledata = (!empty($this->saveablefields)) ? Convert::raw2sql(
                array_intersect_key($data, array_combine($this->saveablefields, $this->saveablefields))
            ) : $data;
            $quantity = isset($data['Quantity']) ? (int)$data['Quantity'] : 1;
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
     * @param Controller $controller the controller instance that is being passed to the form
     * @return FieldList Fields for this form.
     */
    protected function getFormFields($controller = null)
    {
        $fields = FieldList::create();

        if ($this->maxquantity) {
            $values = array();
            $count = 1;

            while ($count <= $this->maxquantity) {
                $values[$count] = $count;
                $count++;
            }

            $fields->push(DropdownField::create('Quantity', _t('SilverShop\Generic.Quantity', 'Quantity'), $values, 1));
        } else {
            $fields->push(
                NumericField::create('Quantity', _t('SilverShop\Generic.Quantity', 'Quantity'), 1)
                    ->setAttribute('type', 'number')
                    ->setAttribute('min', '0')
            );
        }

        return $fields;
    }

    /**
     * @return FieldList Actions for this form.
     */
    protected function getFormActions()
    {
        return FieldList::create(
            FormAction::create('addtocart', _t("SilverShop\Page\Product.AddToCart", 'Add to Cart'))
                ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );
    }

    /**
     * @return Validator Validator for this form.
     */
    protected function getFormValidator()
    {
        return RequiredFields::create(
            [
                'Quantity',
            ]
        );
    }
}

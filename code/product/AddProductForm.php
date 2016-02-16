<?php

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
            $this->getFormFields(),
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
            if (!ShoppingCart_Controller::config()->direct_to_cart_page) {
                $form->SessionMessage($cart->getMessage(), $cart->getMessageType());
            }

            $this->extend('updateAddToCart', $form, $buyable);

            $this->extend('updateAddProductFormResponse', $request, $response, $buyable, $quantity, $form);

            return $response ? $response : ShoppingCart_Controller::direct($cart->getMessageType());
        }
    }

    public function getBuyable($data = null)
    {
        if ($this->controller->dataRecord instanceof Buyable) {
            return $this->controller->dataRecord;
        }
        return DataObject::get_by_id('Product', (int)$this->request->postVar("BuyableID")); //TODO: get buyable
    }

    /**
     * @return FieldList Fields for this form.
     */
    protected function getFormFields()
    {
        $fields = FieldList::create();

        if ($this->maxquantity) {
            $values = array();
            $count = 1;

            while ($count <= $this->maxquantity) {
                $values[$count] = $count;
                $count++;
            }

            $fields->push(DropdownField::create('Quantity', _t('AddProductForm.Quantity', 'Quantity'), $values, 1));
        } else {
            $fields->push(
                NumericField::create(_t('AddProductForm.Quantity', 'Quantity'), 'Quantity', 1)
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
            FormAction::create('addtocart', _t("AddProductForm.ADDTOCART", 'Add to Cart'))
        );
    }

    /**
     * @return Validator Validator for this form.
     */
    protected function getFormValidator()
    {
        return RequiredFields::create(
            array(
                'Quantity',
            )
        );
    }
}

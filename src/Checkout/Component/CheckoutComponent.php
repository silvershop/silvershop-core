<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;
use SilverShop\ShopTools;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ValidationException;

/**
 * CheckoutComponent
 *
 * A modularised piece of checkout functionality.
 *
 * A checkout component will:
 *
 *  - provide form fields
 *  - validate entered data
 *  - save data from given form fields
 */
abstract class CheckoutComponent
{
    use Injectable;

    protected $requiredfields = [];

    protected $dependson = [];

    /**
     * Get form fields for manipulating the current order,
     * according to the responsibility of this component.
     *
     * @param Order $order the form being updated
     *
     * @throws \Exception
     * @return FieldList fields for manipulating order
     */
    abstract public function getFormFields(Order $order);

    /**
     * Is this data valid for saving into an order?
     *
     * This function should never rely on form.
     *
     * @param Order $order the form being updated
     * @param array $data  data to be validated
     *
     * @throws ValidationException
     * @return boolean the data is valid
     */
    abstract public function validateData(Order $order, array $data);

    /**
     * Get required data out of the model.
     *
     * @param Order $order
     *
     * @return array        get data from model(s)
     */
    abstract public function getData(Order $order);

    /**
     * Set the model data for this component.
     *
     * This function should never rely on form.
     *
     * @param Order $order
     * @param array $data  data to be saved into order object
     *
     * @throws \Exception
     * @return Order the updated order
     */
    abstract public function setData(Order $order, array $data);

    /**
     * Get the data fields that are required for the component.
     *
     * @param Order $order [description]
     *
     * @return array        required data fields
     */
    public function getRequiredFields(Order $order)
    {
        return $this->requiredfields;
    }

    /**
     * @return array
     */
    public function dependsOn()
    {
        return $this->dependson;
    }

    /**
     * @return string
     */
    public function name()
    {
        return ShopTools::sanitiseClassName(static::class);
    }

    /**
     * Whether or not this component provides the payment data that should be passed to the payment gateway
     * @return bool
     */
    public function providesPaymentData()
    {
        return false;
    }
}

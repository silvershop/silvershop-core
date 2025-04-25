<?php

namespace SilverShop\Checkout\Component;

use Exception;
use SilverShop\Model\Order;
use SilverShop\ShopTools;
use SilverStripe\Core\Config\Configurable;
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
    use Configurable;

    protected $requiredfields = [];

    protected $dependson = [];

    /**
     * Get form fields for manipulating the current order,
     * according to the responsibility of this component.
     *
     * @param Order $order the form being updated
     *
     * @throws Exception
     * @return FieldList fields for manipulating order
     */
    abstract public function getFormFields(Order $order): FieldList;

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
    abstract public function validateData(Order $order, array $data): bool;

    /**
     * Get required data out of the model.
     *
     *
     * @return array        get data from model(s)
     */
    abstract public function getData(Order $order): array;

    /**
     * Set the model data for this component.
     *
     * This function should never rely on form.
     *
     * @param array $data  data to be saved into order object
     *
     * @throws Exception
     * @return Order the updated order
     */
    abstract public function setData(Order $order, array $data): Order;

    /**
     * Get the data fields that are required for the component.
     *
     * @param Order $order [description]
     *
     * @return array        required data fields
     */
    public function getRequiredFields(Order $order): array
    {
        return $this->requiredfields;
    }

    public function dependsOn(): array
    {
        return $this->dependson;
    }

    public function name(): string
    {
        return ShopTools::sanitiseClassName(static::class);
    }

    /**
     * Whether or not this component provides the payment data that should be passed to the payment gateway
     */
    public function providesPaymentData(): bool
    {
        return false;
    }
}

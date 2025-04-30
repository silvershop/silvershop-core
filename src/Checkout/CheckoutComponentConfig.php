<?php

namespace SilverShop\Checkout;

use SilverShop\Checkout\Component\CheckoutComponent;
use SilverShop\Checkout\Component\CheckoutComponentNamespaced;
use SilverShop\Model\Order;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;

/**
 * @package shop
 */
class CheckoutComponentConfig
{
    use Injectable;

    protected ArrayList $components;
    protected Order $order;
    protected bool $namespaced; //namespace fields according to their component

    public function __construct(Order $order, bool $namespaced = true)
    {
        $this->components = ArrayList::create();
        $this->order = $order;
        $this->namespaced = $namespaced;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param string            $insertBefore The class of the component to insert this one before
     * @return $this
     */
    public function addComponent(CheckoutComponent $checkoutComponent, $insertBefore = null): static
    {
        if ($this->namespaced) {
            $checkoutComponent = CheckoutComponentNamespaced::create($checkoutComponent);
        }
        if ($insertBefore) {
            $existingItems = $this->getComponents();
            $this->components = ArrayList::create();
            $inserted = false;
            foreach ($existingItems as $existingItem) {
                if (!$inserted && $existingItem instanceof $insertBefore) {
                    $this->components->push($checkoutComponent);
                    $inserted = true;
                }
                $this->components->push($existingItem);
            }
            if (!$inserted) {
                $this->components->push($checkoutComponent);
            }
        } else {
            $this->getComponents()->push($checkoutComponent);
        }
        return $this;
    }

    /**
     * @return ArrayList Of CheckoutComponent
     */
    public function getComponents(): ArrayList
    {
        if (!$this->components) {
            $this->components = ArrayList::create();
        }
        return $this->components;
    }

    /**
     * Returns the first available component with the given class or interface.
     *
     * @param string $type ClassName
     * @return ?CheckoutComponent
     */
    public function getComponentByType(string $type)
    {
        foreach ($this->components as $component) {
            if ($this->namespaced) {
                if ($component->Proxy() instanceof $type) {
                    return $component->Proxy();
                }
            } else {
                if ($component instanceof $type) {
                    return $component;
                }
            }
        }
        return null;
    }

    /**
     * Whether or not this config has a component that collects payment data.
     * Should be used to determine whether or not to add an additional payment step after checkout.
     */
    public function hasComponentWithPaymentData(): bool
    {
        foreach ($this->getComponents() as $component) {
            if ($component->providesPaymentData()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get combined form fields
     *
     * @return FieldList namespaced fields
     */
    public function getFormFields(): FieldList
    {
        $fieldList = FieldList::create();
        foreach ($this->getComponents() as $component) {
            if ($cfields = $component->getFormFields($this->order)) {
                $fieldList->merge($cfields);
            } else {
                user_error('getFields on  ' . get_class($component) . ' must return a FieldList');
            }
        }
        return $fieldList;
    }

    public function getRequiredFields(): array
    {
        $required = [];
        foreach ($this->getComponents() as $component) {
            $required = array_merge($required, $component->getRequiredFields($this->order));
        }
        return $required;
    }

    /**
     * Validate every component against given data.
     *
     * @param array $data data to validate
     * @return boolean validation result
     * @throws ValidationException
     */
    public function validateData(array $data): bool
    {
        $validationResult = ValidationResult::create();
        foreach ($this->getComponents() as $component) {
            try {
                $component->validateData($this->order, $this->dependantData($component, $data));
            } catch (ValidationException $e) {
                //transfer messages into a single result
                foreach ($e->getResult()->getMessages() as $code => $message) {
                    if (is_numeric($code)) {
                        $code = null;
                    }
                    if ($this->namespaced) {
                        $code = $component->namespaceFieldName($code);
                    }
                    $validationResult->addError($message['message'], $code);
                }
            }
        }

        $this->order->extend('onValidateDataOnCheckout', $validationResult);

        if (!$validationResult->isValid()) {
            throw ValidationException::create($validationResult);
        }

        return true;
    }

    /**
     * Get combined data
     *
     * @return array map of field names to data values
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->getComponents() as $component) {
            $orderdata = $component->getData($this->order);

            if (is_array($orderdata)) {
                $data = array_merge($data, $orderdata);
            } else {
                user_error('getData on  ' . $component->name() . ' must return an array');
            }
        }
        return $data;
    }

    /**
     * Set data on all components
     *
     * @param array $data map of field names to data values
     */
    public function setData(array $data): void
    {
        foreach ($this->getComponents() as $component) {
            $component->setData($this->order, $this->dependantData($component, $data));
        }
    }

    /**
     * Helper function for saving data from other components.
     */
    protected function dependantData($component, array $data): array
    {

        if (!$this->namespaced) { //no need to try and get un-namespaced dependant data
            return $data;
        }
        $dependantdata = [];
        foreach ($component->dependsOn() as $dependanttype) {
            $dependant = null;
            foreach ($this->components as $check) {
                if (get_class($check->Proxy()) == $dependanttype) {
                    $dependant = $check;
                    break;
                }
            }
            if (!$dependant) {
                user_error("Could not find a $dependanttype component, as depended by " . $component->name());
            }
            $dependantdata = array_merge(
                $dependantdata,
                $component->namespaceData($dependant->unnamespaceData($data))
            );
        }
        return array_merge($dependantdata, $data);
    }
}

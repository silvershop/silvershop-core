<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Model\Order;

/**
 * Proxy class to handle namespacing field names for checkout components
 */
class CheckoutComponentNamespaced extends CheckoutComponent
{
    /**
     * @var CheckoutComponent
     */
    protected $proxy;

    public function __construct(CheckoutComponent $component)
    {
        $this->proxy = $component;
    }

    /**
     * @return CheckoutComponent
     */
    public function Proxy()
    {
        return $this->proxy;
    }

    public function getFormFields(Order $order)
    {
        $fields = $this->proxy->getFormFields($order);
        $allFields = $fields->dataFields();
        if ($allFields) {
            foreach ($allFields as $field) {
                $field->setName($this->namespaceFieldName($field->getName()));
            }
        }

        return $fields;
    }

    public function validateData(Order $order, array $data)
    {
        return $this->proxy->validateData($order, $this->unnamespaceData($data));
    }

    public function getData(Order $order)
    {
        return $this->namespaceData($this->proxy->getData($order));
    }

    public function setData(Order $order, array $data)
    {
        return $this->proxy->setData($order, $this->unnamespaceData($data));
    }

    public function getRequiredFields(Order $order)
    {
        $fields = $this->proxy->getRequiredFields($order);
        $namespaced = array();
        foreach ($fields as $field) {
            $namespaced[] = $this->namespaceFieldName($field);
        }
        return $namespaced;
    }

    public function dependsOn()
    {
        return $this->proxy->dependsOn();
    }

    public function name()
    {
        return $this->proxy->name();
    }

    public function providesPaymentData()
    {
        return $this->proxy->providesPaymentData();
    }

    //namespacing functions

    public function namespaceData(array $data)
    {
        $newdata = array();
        foreach ($data as $key => $value) {
            $newdata[$this->namespaceFieldName($key)] = $value;
        }
        return $newdata;
    }

    public function unnamespaceData(array $data)
    {
        $newdata = array();
        foreach ($data as $key => $value) {
            if (strpos($key, $this->name()) === 0) {
                $newdata[$this->unnamespaceFieldName($key)] = $value;
            }
        }
        return $newdata;
    }

    public function namespaceFieldName($name)
    {
        return $this->name() . "_" . $name;
    }

    public function unnamespaceFieldName($name)
    {
        return substr($name, strlen($this->name() . "_"));
    }
}

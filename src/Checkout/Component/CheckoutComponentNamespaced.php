<?php

namespace SilverShop\Checkout\Component;

use SilverShop\Checkout\Component\CheckoutComponent;
use SilverShop\Model\Order;
use SilverStripe\Forms\FieldList;

/**
 * Proxy class to handle namespacing field names for checkout components
 */
class CheckoutComponentNamespaced extends CheckoutComponent
{
    protected CheckoutComponent $proxy;

    public function __construct(CheckoutComponent $component)
    {
        $this->proxy = $component;
    }

    public function Proxy(): CheckoutComponent
    {
        return $this->proxy;
    }

    public function getFormFields(Order $order): FieldList
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

    public function validateData(Order $order, array $data): bool
    {
        return $this->proxy->validateData($order, $this->unnamespaceData($data));
    }

    public function getData(Order $order): array
    {
        return $this->namespaceData($this->proxy->getData($order));
    }

    public function setData(Order $order, array $data): Order
    {
        return $this->proxy->setData($order, $this->unnamespaceData($data));
    }

    /**
     * @return mixed[]
     */
    public function getRequiredFields(Order $order): array
    {
        $fields = $this->proxy->getRequiredFields($order);
        $namespaced = [];
        foreach ($fields as $field) {
            $namespaced[] = $this->namespaceFieldName($field);
        }
        return $namespaced;
    }

    public function dependsOn(): array
    {
        return $this->proxy->dependsOn();
    }

    public function name(): string
    {
        return $this->proxy->name();
    }

    public function providesPaymentData(): bool
    {
        return $this->proxy->providesPaymentData();
    }

    //namespacing functions
    /**
     * @return mixed[]
     */
    public function namespaceData(array $data): array
    {
        $newdata = [];
        foreach ($data as $key => $value) {
            $newdata[$this->namespaceFieldName($key)] = $value;
        }
        return $newdata;
    }

    /**
     * @return mixed[]
     */
    public function unnamespaceData(array $data): array
    {
        $newdata = [];
        foreach ($data as $key => $value) {
            if (strpos($key, $this->name()) === 0) {
                $newdata[$this->unnamespaceFieldName($key)] = $value;
            }
        }
        return $newdata;
    }

    public function namespaceFieldName(?string $name): string
    {
        return $this->name() . "_" . $name;
    }

    public function unnamespaceFieldName($name): string
    {
        return substr($name, strlen($this->name() . "_"));
    }
}

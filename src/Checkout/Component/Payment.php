<?php

declare(strict_types=1);

namespace SilverShop\Checkout\Component;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Core\Validation\ValidationException;
use SilverShop\Checkout\Checkout;
use SilverShop\Model\Order;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Omnipay\GatewayInfo;

class Payment extends CheckoutComponent
{
    public function getFormFields(Order $order): FieldList
    {
        $fieldList = FieldList::create();
        $gateways = GatewayInfo::getSupportedGateways();
        if (count($gateways) > 1) {
            $fieldList->push(
                OptionsetField::create(
                    'PaymentMethod',
                    _t("SilverShop\Checkout\CheckoutField.PaymentType", "Payment Type"),
                    $gateways,
                    array_keys($gateways)
                )->addExtraClass('silvershop-payment-method')
            );
        }

        if (count($gateways) === 1) {
            $fieldList->push(
                HiddenField::create('PaymentMethod')->setValue(key($gateways))
            );
        }

        return $fieldList;
    }

    public function getRequiredFields(Order $order): array
    {
        if (count(GatewayInfo::getSupportedGateways()) > 1) {
            return [];
        }

        return ['PaymentMethod'];
    }

    public function validateData(Order $order, array $data): bool
    {
        $validationResult = ValidationResult::create();
        if (!isset($data['PaymentMethod'])) {
            $validationResult->addError(
                _t(__CLASS__ . '.NoPaymentMethod', "Payment method not provided"),
                "PaymentMethod"
            );
            throw ValidationException::create($validationResult);
        }

        $methods = GatewayInfo::getSupportedGateways();
        if (!isset($methods[$data['PaymentMethod']])) {
            $validationResult->addError(_t(__CLASS__ . '.UnsupportedGateway', "Gateway not supported"), "PaymentMethod");
            throw ValidationException::create($validationResult);
        }

        return true;
    }

    public function getData(Order $order): array
    {
        return [
            'PaymentMethod' => Checkout::get($order)->getSelectedPaymentMethod(),
        ];
    }

    public function setData(Order $order, array $data): Order
    {
        if (isset($data['PaymentMethod'])) {
            Checkout::get($order)->setPaymentMethod($data['PaymentMethod']);
        }

        return $order;
    }
}

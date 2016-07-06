<?php

use SilverStripe\Omnipay\GatewayInfo;

class PaymentCheckoutComponent extends CheckoutComponent
{
    public function getFormFields(Order $order)
    {
        $fields = FieldList::create();
        $gateways = GatewayInfo::getSupportedGateways();
        if (count($gateways) > 1) {
            $fields->push(
                OptionsetField::create(
                    'PaymentMethod',
                    _t("CheckoutField.PaymentType", "Payment Type"),
                    $gateways,
                    array_keys($gateways)
                )
            );
        }
        if (count($gateways) == 1) {
            $fields->push(
                HiddenField::create('PaymentMethod')->setValue(key($gateways))
            );
        }

        return $fields;
    }

    public function getRequiredFields(Order $order)
    {
        if (count(GatewayInfo::getSupportedGateways()) > 1) {
            return array();
        }

        return array('PaymentMethod');
    }

    public function validateData(Order $order, array $data)
    {
        $result = ValidationResult::create();
        if (!isset($data['PaymentMethod'])) {
            $result->error(
                _t('PaymentCheckoutComponent.NoPaymentMethod', "Payment method not provided"),
                "PaymentMethod"
            );
            throw new ValidationException($result);
        }
        $methods = GatewayInfo::getSupportedGateways();
        if (!isset($methods[$data['PaymentMethod']])) {
            $result->error(_t('PaymentCheckoutComponent.UnsupportedGateway', "Gateway not supported"), "PaymentMethod");
            throw new ValidationException($result);
        }
    }

    public function getData(Order $order)
    {
        return array(
            'PaymentMethod' => Checkout::get($order)->getSelectedPaymentMethod(),
        );
    }

    public function setData(Order $order, array $data)
    {
        if (isset($data['PaymentMethod'])) {
            Checkout::get($order)->setPaymentMethod($data['PaymentMethod']);
        }
    }
}

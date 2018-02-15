<?php

namespace SilverShop\Checkout\Component;

use Omnipay\Common\Helper;
use SilverShop\Checkout\Checkout;
use SilverShop\Model\Order;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;

/**
 * This component should only ever be used on SSL encrypted pages!
 */
class OnsitePayment extends CheckoutComponent
{
    public function getFormFields(Order $order)
    {
        $gateway = Checkout::get($order)->getSelectedPaymentMethod();
        $gatewayfieldsfactory = new GatewayFieldsFactory($gateway, ['Card']);
        $fields = $gatewayfieldsfactory->getCardFields();
        if ($gateway === 'Dummy') {
            $fields->unshift(
                LiteralField::create(
                    'dummypaymentmessage',
                    '<p class=\"message good\">Dummy data has been added to the form for testing convenience.</p>'
                )
            );
        }

        return $fields;
    }

    public function getRequiredFields(Order $order)
    {
        $gateway = Checkout::get($order)->getSelectedPaymentMethod();
        $required = GatewayInfo::requiredFields($gateway);
        $fieldsFactory = new GatewayFieldsFactory($gateway, ['Card']);
        return $fieldsFactory->getFieldName(array_combine($required, $required));
    }

    public function validateData(Order $order, array $data)
    {
        $result = ValidationResult::create();
        //TODO: validate credit card data
        if (!Helper::validateLuhn($data['number'])) {
            $result->addError(_t(__CLASS__ . '.InvalidCreditCard', 'Credit card is invalid'));
            throw new ValidationException($result);
        }
    }

    public function getData(Order $order)
    {
        $data = array();
        $gateway = Checkout::get($order)->getSelectedPaymentMethod();
        //provide valid dummy credit card data
        if ($gateway === 'Dummy') {
            $data = array_merge(
                [
                    'name' => 'Joe Bloggs',
                    'number' => '4242424242424242',
                    'cvv' => 123,
                ],
                $data
            );
        }
        return $data;
    }

    public function setData(Order $order, array $data)
    {
        //create payment?
    }

    public function providesPaymentData()
    {
        return true;
    }
}

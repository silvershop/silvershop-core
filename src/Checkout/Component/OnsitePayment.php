<?php

namespace SilverShop\Checkout\Component;

use Omnipay\Common\Helper;
use SilverShop\Checkout\Checkout;
use SilverShop\Model\Order;
use SilverStripe\Forms\FieldList;
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
    public function getFormFields(Order $order): FieldList
    {
        $gateway = Checkout::get($order)->getSelectedPaymentMethod();
        $gatewayfieldsfactory = GatewayFieldsFactory::create($gateway, ['Card']);
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

    public function getRequiredFields(Order $order): array
    {
        $gateway = Checkout::get($order)->getSelectedPaymentMethod();
        $required = GatewayInfo::requiredFields($gateway);
        $fieldsFactory = GatewayFieldsFactory::create($gateway, ['Card']);
        return $fieldsFactory->getFieldName(array_combine($required, $required));
    }

    public function validateData(Order $order, array $data): bool
    {
        $result = ValidationResult::create();
        //TODO: validate credit card data
        if (!Helper::validateLuhn($data['number'])) {
            $result->addError(_t(__CLASS__ . '.InvalidCreditCard', 'Credit card is invalid'));
            throw ValidationException::create($result);
        }
        return true;
    }

    public function getData(Order $order): array
    {
        $data = [];
        $gateway = Checkout::get($order)->getSelectedPaymentMethod();
        //provide valid dummy credit card data
        if ($gateway === 'Dummy') {
            return array_merge(
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

    public function setData(Order $order, array $data): Order
    {
        //create payment?
        return $order;
    }

    public function providesPaymentData(): bool
    {
        return true;
    }
}

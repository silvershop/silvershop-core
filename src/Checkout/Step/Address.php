<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Forms\CheckoutForm;
use SilverShop\Checkout\Component\BillingAddress;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\ShippingAddress;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;

class Address extends CheckoutStep
{
    private static array $allowed_actions = [
        'shippingaddress',
        'ShippingAddressForm',
        'setshippingaddress',
        'billingaddress',
        'BillingAddressForm',
        'setbillingaddress',
    ];

    public function shippingconfig(): CheckoutComponentConfig
    {
        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr());
        $checkoutComponentConfig->addComponent(ShippingAddress::create());

        return $checkoutComponentConfig;
    }

    public function shippingaddress(): array
    {
        $checkoutForm = $this->ShippingAddressForm();
        $checkoutForm->Fields()->push(
            CheckboxField::create(
                'SeperateBilling',
                _t(__CLASS__ . '.SeperateBilling', 'Bill to a different address from this')
            )
        );
        $order = $this->shippingconfig()->getOrder();
        if ($order->BillingAddressID !== $order->ShippingAddressID) {
            $checkoutForm->loadDataFrom(['SeperateBilling' => 1]);
        }

        return ['OrderForm' => $checkoutForm];
    }

    public function ShippingAddressForm(): CheckoutForm
    {
        $checkoutForm = CheckoutForm::create($this->owner, 'ShippingAddressForm', $this->shippingconfig());
        $checkoutForm->setActions(
            FieldList::create(
                FormAction::create('setshippingaddress', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateShippingAddressForm', $checkoutForm);

        return $checkoutForm;
    }

    public function setshippingaddress($data, $form): HTTPResponse
    {
        $this->shippingconfig()->setData($form->getData());
        $step = null;
        if (isset($data['SeperateBilling']) && $data['SeperateBilling']) {
            $step = 'billingaddress';
        } else {
            //ensure billing address = shipping address, when appropriate
            $order = $this->shippingconfig()->getOrder();
            $order->BillingAddressID = $order->ShippingAddressID;
            $order->write();
        }
        return $this->owner->redirect($this->NextStepLink($step));
    }

    public function billingconfig(): CheckoutComponentConfig
    {
        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr());
        $checkoutComponentConfig->addComponent(BillingAddress::create());

        return $checkoutComponentConfig;
    }

    public function billingaddress(): array
    {
        return ['OrderForm' => $this->BillingAddressForm()];
    }

    public function BillingAddressForm(): CheckoutForm
    {
        $checkoutForm = CheckoutForm::create($this->owner, 'BillingAddressForm', $this->billingconfig());
        $checkoutForm->setActions(
            FieldList::create(
                FormAction::create('setbillingaddress', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateBillingAddressForm', $checkoutForm);

        return $checkoutForm;
    }

    public function setbillingaddress($data, $form): HTTPResponse
    {
        $this->billingconfig()->setData($form->getData());
        return $this->owner->redirect($this->NextStepLink());
    }
}

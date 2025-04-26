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
        $config = CheckoutComponentConfig::create(ShoppingCart::curr());
        $config->addComponent(ShippingAddress::create());

        return $config;
    }

    public function shippingaddress(): array
    {
        $form = $this->ShippingAddressForm();
        $form->Fields()->push(
            CheckboxField::create(
                'SeperateBilling',
                _t(__CLASS__ . '.SeperateBilling', 'Bill to a different address from this')
            )
        );
        $order = $this->shippingconfig()->getOrder();
        if ($order->BillingAddressID !== $order->ShippingAddressID) {
            $form->loadDataFrom(['SeperateBilling' => 1]);
        }

        return ['OrderForm' => $form];
    }

    public function ShippingAddressForm(): CheckoutForm
    {
        $form = CheckoutForm::create($this->owner, 'ShippingAddressForm', $this->shippingconfig());
        $form->setActions(
            FieldList::create(
                FormAction::create('setshippingaddress', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateShippingAddressForm', $form);

        return $form;
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
        $config = CheckoutComponentConfig::create(ShoppingCart::curr());
        $config->addComponent(BillingAddress::create());

        return $config;
    }

    public function billingaddress(): array
    {
        return ['OrderForm' => $this->BillingAddressForm()];
    }

    public function BillingAddressForm(): CheckoutForm
    {
        $form = CheckoutForm::create($this->owner, 'BillingAddressForm', $this->billingconfig());
        $form->setActions(
            FieldList::create(
                FormAction::create('setbillingaddress', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateBillingAddressForm', $form);

        return $form;
    }

    public function setbillingaddress($data, $form): HTTPResponse
    {
        $this->billingconfig()->setData($form->getData());
        return $this->owner->redirect($this->NextStepLink());
    }
}

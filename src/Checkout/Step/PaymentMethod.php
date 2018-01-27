<?php

namespace SilverShop\Core\Checkout\Step;

use SilverShop\Core\Cart\ShoppingCart;
use SilverShop\Core\Checkout\Checkout;
use SilverShop\Core\Checkout\CheckoutForm;
use SilverShop\Core\Checkout\Component\CheckoutComponentConfig;
use SilverShop\Core\Checkout\Component\Payment;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;


class PaymentMethod extends CheckoutStep
{
    private static $allowed_actions = [
        'paymentmethod',
        'PaymentMethodForm',
    ];

    protected function checkoutconfig()
    {
        $config = CheckoutComponentConfig::create(ShoppingCart::curr(), false);
        $config->addComponent(Payment::create());

        return $config;
    }

    public function paymentmethod()
    {
        $gateways = GatewayInfo::getSupportedGateways();
        if (count($gateways) == 1) {
            return $this->owner->redirect($this->NextStepLink());
        }
        return array(
            'OrderForm' => $this->PaymentMethodForm(),
        );
    }

    public function PaymentMethodForm()
    {
        $form = CheckoutForm::create($this->owner, 'PaymentMethodForm', $this->checkoutconfig());
        $form->setActions(
            FieldList::create(
                FormAction::create('setpaymentmethod', _t('CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updatePaymentMethodForm', $form);

        return $form;
    }

    public function setpaymentmethod($data, $form)
    {
        $this->checkoutconfig()->setData($form->getData());
        return $this->owner->redirect($this->NextStepLink());
    }

    public function SelectedPaymentMethod()
    {
        return Checkout::get($this->owner->Cart())->getSelectedPaymentMethod(true);
    }
}

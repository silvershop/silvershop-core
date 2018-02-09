<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\Payment;
use SilverShop\Forms\CheckoutForm;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Omnipay\GatewayInfo;

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
                FormAction::create('setpaymentmethod', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
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

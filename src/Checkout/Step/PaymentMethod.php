<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\Payment;
use SilverShop\Forms\CheckoutForm;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Omnipay\GatewayInfo;

class PaymentMethod extends CheckoutStep
{
    private static array $allowed_actions = [
        'paymentmethod',
        'PaymentMethodForm',
    ];

    protected function checkoutconfig(): CheckoutComponentConfig
    {
        $config = CheckoutComponentConfig::create(ShoppingCart::curr(), false);
        $config->addComponent(Payment::create());

        return $config;
    }

    public function paymentmethod(): HTTPResponse|array
    {
        $gateways = GatewayInfo::getSupportedGateways();
        if (count($gateways) == 1) {
            return $this->owner->redirect($this->NextStepLink());
        }
        return [
            'OrderForm' => $this->PaymentMethodForm(),
        ];
    }

    public function PaymentMethodForm(): CheckoutForm
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

    public function setpaymentmethod($data, $form): HTTPResponse
    {
        $this->checkoutconfig()->setData($form->getData());
        return $this->owner->redirect($this->NextStepLink());
    }

    public function SelectedPaymentMethod(): string|array
    {
        return Checkout::get($this->owner->Cart())->getSelectedPaymentMethod(true);
    }
}

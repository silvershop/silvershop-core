<?php

use SilverStripe\Omnipay\GatewayInfo;

class CheckoutStep_PaymentMethod extends CheckoutStep
{
    private static $allowed_actions = array(
        'paymentmethod',
        'PaymentMethodForm',
    );

    protected function checkoutconfig()
    {
        $config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
        $config->addComponent(PaymentCheckoutComponent::create());

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
        $form = CheckoutForm::create($this->owner, "PaymentMethodForm", $this->checkoutconfig());
        $form->setActions(
            FieldList::create(
                FormAction::create("setpaymentmethod", _t('CheckoutStep.Continue', "Continue"))
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

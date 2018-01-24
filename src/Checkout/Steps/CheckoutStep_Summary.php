<?php

class CheckoutStep_Summary extends CheckoutStep
{
    private static $allowed_actions = array(
        'summary',
        'ConfirmationForm',
    );

    public function summary()
    {
        $form = $this->ConfirmationForm();
        return array(
            'OrderForm' => $form,
        );
    }

    public function ConfirmationForm()
    {
        $config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
        $config->addComponent(NotesCheckoutComponent::create());
        $config->addComponent(TermsCheckoutComponent::create());
        $this->owner->extend('updateConfirmationComponentConfig', $config);

        $form = PaymentForm::create($this->owner, "ConfirmationForm", $config);
        $form->setFailureLink($this->owner->Link('summary'));
        $this->owner->extend('updateConfirmationForm', $form);

        return $form;
    }
}

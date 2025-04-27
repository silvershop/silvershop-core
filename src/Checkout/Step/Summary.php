<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\Notes;
use SilverShop\Checkout\Component\Terms;
use SilverShop\Forms\PaymentForm;

class Summary extends CheckoutStep
{
    private static array $allowed_actions = [
        'summary',
        'ConfirmationForm',
    ];

    public function summary(): array
    {
        $paymentForm = $this->ConfirmationForm();
        return [
            'OrderForm' => $paymentForm,
        ];
    }

    public function ConfirmationForm(): PaymentForm
    {
        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr(), false);
        $checkoutComponentConfig->addComponent(Notes::create());
        $checkoutComponentConfig->addComponent(Terms::create());
        $this->owner->extend('updateConfirmationComponentConfig', $checkoutComponentConfig);

        $paymentForm = PaymentForm::create($this->owner, 'ConfirmationForm', $checkoutComponentConfig);
        $paymentForm->setFailureLink($this->owner->Link('summary'));
        $this->owner->extend('updateConfirmationForm', $paymentForm);

        return $paymentForm;
    }
}

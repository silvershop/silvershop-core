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
        $form = $this->ConfirmationForm();
        return [
            'OrderForm' => $form,
        ];
    }

    public function ConfirmationForm(): PaymentForm
    {
        $config = CheckoutComponentConfig::create(ShoppingCart::curr(), false);
        $config->addComponent(Notes::create());
        $config->addComponent(Terms::create());
        $this->owner->extend('updateConfirmationComponentConfig', $config);

        $form = PaymentForm::create($this->owner, 'ConfirmationForm', $config);
        $form->setFailureLink($this->owner->Link('summary'));
        $this->owner->extend('updateConfirmationForm', $form);

        return $form;
    }
}

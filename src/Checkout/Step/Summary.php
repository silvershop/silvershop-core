<?php

declare(strict_types=1);

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

        $this->getOwner()->extend('updateConfirmationComponentConfig', $checkoutComponentConfig);

        $paymentForm = PaymentForm::create($this->getOwner(), 'ConfirmationForm', $checkoutComponentConfig);
        $paymentForm->setFailureLink($this->getOwner()->Link('summary'));

        $this->getOwner()->extend('updateConfirmationForm', $paymentForm);

        return $paymentForm;
    }
}

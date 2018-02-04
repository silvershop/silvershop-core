<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\Notes;
use SilverShop\Checkout\Component\Terms;
use SilverShop\Forms\PaymentForm;

class Summary extends CheckoutStep
{
    private static $allowed_actions = [
        'summary',
        'ConfirmationForm',
    ];

    public function summary()
    {
        $form = $this->ConfirmationForm();
        return array(
            'OrderForm' => $form,
        );
    }

    public function ConfirmationForm()
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

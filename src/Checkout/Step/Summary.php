<?php

namespace SilverShop\Core\Checkout\Step;

use SilverShop\Core\Cart\ShoppingCart;
use SilverShop\Core\Checkout\Component\CheckoutComponentConfig;
use SilverShop\Core\Checkout\Component\Notes;
use SilverShop\Core\Checkout\Component\Terms;
use SilverShop\Core\Checkout\PaymentForm;

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

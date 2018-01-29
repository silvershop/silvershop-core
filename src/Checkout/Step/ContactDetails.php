<?php

namespace SilverShop\Core\Checkout\Step;


use SilverShop\Core\Cart\ShoppingCart;
use SilverShop\Core\Checkout\CheckoutForm;
use SilverShop\Core\Checkout\Component\CheckoutComponentConfig;
use SilverShop\Core\Checkout\Component\CustomerDetails;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Member;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Security;


class ContactDetails extends CheckoutStep
{
    /**
     * Whether or not this step should be skipped if user is logged in
     * @config
     * @var bool
     */
    private static $skip_if_logged_in = false;

    private static $allowed_actions = [
        'contactdetails',
        'ContactDetailsForm',
    ];

    public function contactdetails()
    {
        $form = $this->ContactDetailsForm();
        if (
            ShoppingCart::curr()
            && self::config()->skip_if_logged_in
        ) {
            if (Security::getCurrentUser()) {
                if(!$form->getValidator()->validate()) {
                    return Controller::curr()->redirect($this->NextStepLink());
                } else {
                    $form->clearMessage();
                }
            }
        }

        return [
            'OrderForm' => $form,
        ];
    }

    public function ContactDetailsForm()
    {
        $cart = ShoppingCart::curr();
        if (!$cart) {
            return false;
        }
        $config = CheckoutComponentConfig::create(ShoppingCart::curr());
        $config->addComponent(CustomerDetails::create());
        $form = CheckoutForm::create($this->owner, 'ContactDetailsForm', $config);
        $form->setRedirectLink($this->NextStepLink());
        $form->setActions(
            FieldList::create(
                FormAction::create('checkoutSubmit', _t('CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateContactDetailsForm', $form);

        return $form;
    }
}

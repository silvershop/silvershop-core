<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\CustomerDetails;
use SilverShop\Forms\CheckoutForm;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\Security;

class ContactDetails extends CheckoutStep
{
    /**
     * Whether or not this step should be skipped if user is logged in
     *
     * @config
     * @var    bool
     */
    private static $skip_if_logged_in = false;

    private static $allowed_actions = [
        'contactdetails',
        'ContactDetailsForm',
    ];

    public function contactdetails()
    {
        $form = $this->ContactDetailsForm();
        if (ShoppingCart::curr()
            && self::config()->skip_if_logged_in
        ) {
            if (Security::getCurrentUser()) {
                if ($form->getValidator()->validate()) {
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
                FormAction::create('checkoutSubmit', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateContactDetailsForm', $form);

        return $form;
    }
}

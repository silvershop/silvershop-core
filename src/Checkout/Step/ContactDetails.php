<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\CustomerDetails;
use SilverShop\Forms\CheckoutForm;
use SilverShop\Model\Order;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\Security;

class ContactDetails extends CheckoutStep
{
    /**
     * Whether or not this step should be skipped if user is logged in
     */
    private static bool $skip_if_logged_in = false;

    private static array $allowed_actions = [
        'contactdetails',
        'ContactDetailsForm',
    ];

    public function contactdetails(): HTTPResponse|array
    {
        $form = $this->ContactDetailsForm();
        if (!ShoppingCart::curr() instanceof Order) {
            return [
                'OrderForm' => $form,
            ];
        }
        if (!self::config()->skip_if_logged_in) {
            return [
                'OrderForm' => $form,
            ];
        }
        if (!Security::getCurrentUser()) {
            return [
                'OrderForm' => $form,
            ];
        }
        if ($form->getValidator()->validate()) {
            return Controller::curr()->redirect($this->NextStepLink());
        }
        $form->clearMessage();

        return [
            'OrderForm' => $form,
        ];
    }

    public function ContactDetailsForm(): false|CheckoutForm
    {
        $cart = ShoppingCart::curr();
        if (!$cart instanceof Order) {
            return false;
        }
        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr());
        $checkoutComponentConfig->addComponent(CustomerDetails::create());
        $checkoutForm = CheckoutForm::create($this->owner, 'ContactDetailsForm', $checkoutComponentConfig);
        $checkoutForm->setRedirectLink($this->NextStepLink());
        $checkoutForm->setActions(
            FieldList::create(
                FormAction::create('checkoutSubmit', _t('SilverShop\Checkout\Step\CheckoutStep.Continue', 'Continue'))
            )
        );
        $this->owner->extend('updateContactDetailsForm', $checkoutForm);

        return $checkoutForm;
    }
}

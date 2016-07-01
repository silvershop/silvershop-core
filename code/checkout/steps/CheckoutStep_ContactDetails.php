<?php

class CheckoutStep_ContactDetails extends CheckoutStep
{
    private static $allowed_actions = array(
        'contactdetails',
        'ContactDetailsForm',
    );

    public function contactdetails()
    {
        $form = $this->ContactDetailsForm();
        if (
            ShoppingCart::curr()
            && Config::inst()->get("CheckoutStep_ContactDetails", "skip_if_logged_in")
        ) {
            if (Member::currentUser()) {
                if(!$form->getValidator()->validate()) {
                    return Controller::curr()->redirect($this->NextStepLink());
                } else {
                    $form->clearMessage();
                }
            }
        }

        return array(
            'OrderForm' => $form,
        );
    }

    public function ContactDetailsForm()
    {
        $cart = ShoppingCart::curr();
        if (!$cart) {
            return false;
        }
        $config = new CheckoutComponentConfig(ShoppingCart::curr());
        $config->addComponent(CustomerDetailsCheckoutComponent::create());
        $form = CheckoutForm::create($this->owner, 'ContactDetailsForm', $config);
        $form->setRedirectLink($this->NextStepLink());
        $form->setActions(
            FieldList::create(
                FormAction::create("checkoutSubmit", _t('CheckoutStep.Continue', "Continue"))
            )
        );
        $this->owner->extend('updateContactDetailsForm', $form);

        return $form;
    }
}

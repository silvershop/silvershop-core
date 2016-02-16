<?php

/**
 * Login, sign-up, or proceed as guest
 */
class CheckoutStep_Membership extends CheckoutStep
{
    private static $allowed_actions   = array(
        'membership',
        'MembershipForm',
        'LoginForm',
        'createaccount',
        'docreateaccount',
        'CreateAccountForm',
    );

    public static  $url_handlers      = array(
        'login' => 'index',
    );

    public static  $skip_if_logged_in = true;

    public function membership()
    {
        //if logged in, then redirect to next step
        if (ShoppingCart::curr() && self::$skip_if_logged_in && Member::currentUser()) {
            Controller::curr()->redirect($this->NextStepLink());
            return;
        }
        return $this->owner->customise(
            array(
                'Form'      => $this->MembershipForm(),
                'LoginForm' => $this->LoginForm(),
                'GuestLink' => $this->NextStepLink(),
            )
        )->renderWith(
            array("CheckoutPage_membership", "CheckoutPage", "Page")
        ); //needed to make rendering work on index
    }

    public function MembershipForm()
    {
        $fields = FieldList::create();
        $actions = FieldList::create(
            FormAction::create(
                "createaccount",
                _t(
                    'CheckoutStep_Membership.CREATE_ACCOUNT',
                    "Create an Account",
                    'This is an option presented to the user'
                )
            ),
            FormAction::create("guestcontinue", _t('CheckoutStep_Membership.CONTINUE_AS_GUEST', "Continue as Guest"))
        );
        $form = Form::create($this->owner, 'MembershipForm', $fields, $actions);
        $this->owner->extend('updateMembershipForm', $form);
        return $form;
    }

    public function guestcontinue()
    {
        $this->owner->redirect($this->NextStepLink());
    }

    public function LoginForm()
    {
        $form = MemberLoginForm::create($this->owner, 'LoginForm');
        $this->owner->extend('updateLoginForm', $form);
        return $form;
    }

    public function createaccount($requestdata)
    {
        //we shouldn't create an account if already a member
        if (Member::currentUser()) {
            Controller::curr()->redirect($this->NextStepLink());
            return;
        }
        //using this function to redirect, and display action
        if (!($requestdata instanceof SS_HTTPRequest)) {
            Controller::curr()->redirect($this->NextStepLink('createaccount'));
            return;
        }
        return array(
            'Form' => $this->CreateAccountForm(),
        );
    }

    public function registerconfig()
    {
        $order = ShoppingCart::curr();
        //hack to make components work when there is no order
        if (!$order) {
            $order = Order::create();
        }
        $config = new CheckoutComponentConfig($order, false);
        $config->addComponent(CustomerDetailsCheckoutComponent::create());
        $config->addComponent(MembershipCheckoutComponent::create());

        return $config;
    }

    public function CreateAccountForm()
    {
        $form = CheckoutForm::create($this->owner, "CreateAccountForm", $this->registerconfig());
        $form->setActions(
            FieldList::create(
                FormAction::create(
                    'docreateaccount',
                    _t(
                        'CheckoutStep_Membership.CREATE_NEW_ACCOUNT',
                        'Create New Account',
                        'This is an action (Button label)'
                    )
                )
            )
        );
        $form->getValidator()->addRequiredField("Password");

        $this->owner->extend('updateCreateAccountForm', $form);
        return $form;
    }

    public function docreateaccount($data, Form $form)
    {
        $this->registerconfig()->setData($form->getData());

        return Controller::curr()->redirect($this->NextStepLink());
    }
}

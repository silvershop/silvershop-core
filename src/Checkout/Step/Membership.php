<?php

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\CustomerDetails;
use SilverShop\Forms\CheckoutForm;
use SilverShop\Model\Order;
use SilverShop\Page\CheckoutPage;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;

/**
 * Login, sign-up, or proceed as guest
 */
class Membership extends CheckoutStep
{
    private static $allowed_actions = [
        'membership',
        'MembershipForm',
        'LoginForm',
        'createaccount',
        'docreateaccount',
        'CreateAccountForm',
    ];

    private static $url_handlers = [
        'login' => 'index',
    ];

    /**
     * Whether or not this step should be skipped if user is logged in
     *
     * @config
     * @var    bool
     */
    private static $skip_if_logged_in = true;

    public function membership()
    {
        //if logged in, then redirect to next step
        if (ShoppingCart::curr() && self::config()->skip_if_logged_in && Security::getCurrentUser()) {
            return Controller::curr()->redirect($this->NextStepLink());
        }
        return [
            'Form' => $this->MembershipForm(),
            'LoginForm' => $this->LoginForm(),
            'GuestLink' => $this->NextStepLink(),
        ];
    }

    public function MembershipForm()
    {
        $fields = FieldList::create();
        $actions = FieldList::create(
            FormAction::create(
                'createaccount',
                _t(
                    __CLASS__ . '.CreateAccount',
                    'Create an Account',
                    'This is an option presented to the user'
                )
            ),
            FormAction::create('guestcontinue', _t(__CLASS__ . '.ContinueAsGuest', 'Continue as Guest'))
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
        $form = MemberLoginForm::create($this->owner, MemberAuthenticator::class, 'LoginForm');
        $this->owner->extend('updateLoginForm', $form);
        return $form;
    }

    public function createaccount($requestdata)
    {
        //we shouldn't create an account if already a member
        if (Security::getCurrentUser()) {
            return Controller::curr()->redirect($this->NextStepLink());
        }
        //using this function to redirect, and display action
        if (!($requestdata instanceof HTTPRequest)) {
            return Controller::curr()->redirect($this->NextStepLink('createaccount'));
        }
        return [
            'Form' => $this->CreateAccountForm(),
        ];
    }

    public function registerconfig()
    {
        $order = ShoppingCart::curr();
        //hack to make components work when there is no order
        if (!$order) {
            $order = Order::create();
        }
        $config = CheckoutComponentConfig::create($order, false);
        $config->addComponent(CustomerDetails::create());
        $config->addComponent(\SilverShop\Checkout\Component\Membership::create());

        return $config;
    }

    public function CreateAccountForm()
    {
        $form = CheckoutForm::create($this->owner, 'CreateAccountForm', $this->registerconfig());
        $form->setActions(
            FieldList::create(
                FormAction::create(
                    'docreateaccount',
                    _t(
                        __CLASS__ . '.CreateNewAccount',
                        'Create New Account',
                        'This is an action (Button label)'
                    )
                )
            )
        );
        $form->getValidator()->addRequiredField('Password');

        $this->owner->extend('updateCreateAccountForm', $form);
        return $form;
    }

    public function docreateaccount($data, Form $form)
    {
        $this->registerconfig()->setData($form->getData());

        return Controller::curr()->redirect($this->NextStepLink());
    }
}

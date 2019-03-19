<?php

namespace SilverShop\Page;

use PageController;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\OnsitePayment;
use SilverShop\Checkout\SinglePageCheckoutComponentConfig;
use SilverShop\Checkout\Step\Address;
use SilverShop\Checkout\Step\AddressBook;
use SilverShop\Checkout\Step\ContactDetails;
use SilverShop\Checkout\Step\Membership;
use SilverShop\Checkout\Step\PaymentMethod;
use SilverShop\Checkout\Step\Summary;
use SilverShop\Extension\SteppedCheckoutExtension;
use SilverShop\Extension\ViewableCartExtension;
use SilverShop\Forms\PaymentForm;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Omnipay\Model\Message\GatewayErrorMessage;

/**
 * @package shop
 * @mixin CheckoutPage
 * @mixin SteppedCheckoutExtension
 * @mixin Address
 * @mixin AddressBook
 * @mixin ContactDetails
 * @mixin Membership
 * @mixin PaymentMethod
 * @mixin Summary
 * @mixin ViewableCartExtension
 */
class CheckoutPageController extends PageController
{
    private static $url_segment     = 'checkout';

    private static $allowed_actions = array(
        'OrderForm',
        'payment',
        'PaymentForm',
    );

    public function Title()
    {
        if ($this->failover && $this->failover->Title) {
            return $this->failover->Title;
        }

        return _t('SilverShop\Page\CheckoutPage.DefaultTitle', "Checkout");
    }

    public function OrderForm()
    {
        if (!(bool)$this->Cart()) {
            return false;
        }

        /**
         * @var CheckoutComponentConfig $config
         */
        $config = SinglePageCheckoutComponentConfig::create(ShoppingCart::curr());
        $form = PaymentForm::create($this, 'OrderForm', $config);

        // Normally, the payment is on a second page, either offsite or through /checkout/payment
        // If the site has customised the checkout component config to include an onsite payment
        // component, we should honor that and change the button label. PaymentForm::checkoutSubmit
        // will also check this and process payment if needed.
        if ($config->hasComponentWithPaymentData()) {
            $form->setActions(
                FieldList::create(
                    FormAction::create('checkoutSubmit', _t('SilverShop\Page\CheckoutPage.SubmitPayment', 'Submit Payment'))
                )
            );
        }

        $form->Cart = $this->Cart();
        $this->extend('updateOrderForm', $form);

        return $form;
    }

    /**
     * Action for making on-site payments
     */
    public function payment()
    {
        if (!$this->Cart()) {
            return $this->redirect($this->Link());
        }

        return array(
            'Title'     => 'Make Payment',
            'OrderForm' => $this->PaymentForm(),
        );
    }

    public function PaymentForm()
    {
        if (!(bool)$this->Cart()) {
            return false;
        }

        $config = CheckoutComponentConfig::create(ShoppingCart::curr(), false);
        $config->addComponent(OnsitePayment::create());

        $form = PaymentForm::create($this, "PaymentForm", $config);

        $form->setActions(
            FieldList::create(
                FormAction::create("submitpayment", _t('SilverShop\Page\CheckoutPage.SubmitPayment', "Submit Payment"))
            )
        );

        $form->setFailureLink($this->Link());
        $this->extend('updatePaymentForm', $form);

        return $form;
    }

    /**
     * Retrieves error messages for the latest payment (if existing).
     * This can originate e.g. from an earlier offsite gateway API response.
     *
     * @return string
     */
    public function PaymentErrorMessage()
    {
        $order = $this->Cart();
        if (!$order) {
            return false;
        }

        $lastPayment = $order->Payments()->sort('Created', 'DESC')->first();
        if (!$lastPayment) {
            return false;
        }

        $errorMessages = $lastPayment->Messages()->exclude('Message', '')->sort('Created', 'DESC');
        $lastErrorMessage = null;
        foreach ($errorMessages as $errorMessage) {
            if ($errorMessage instanceof GatewayErrorMessage) {
                $lastErrorMessage = $errorMessage;
                break;
            }
        }
        if (!$lastErrorMessage) {
            return false;
        }

        return $lastErrorMessage->Message;
    }

    /**
     * Override viewer to get correct template for first step
     *
     * {@inheritDoc}
     * @see \SilverStripe\CMS\Controllers\ContentController::getViewer()
     */
    public function getViewer($action)
    {
        if (CheckoutPage::config()->first_step && $action == 'index') {
            $action = CheckoutPage::config()->first_step;
        }
        return parent::getViewer($action);
    }
}

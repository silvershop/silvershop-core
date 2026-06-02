<?php

declare(strict_types=1);

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\Notes;
use SilverShop\Checkout\Component\Terms;
use SilverShop\Forms\PaymentForm;
use SilverShop\Forms\SummaryPaymentForm;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;

class Summary extends CheckoutStep
{
    private static array $allowed_actions = [
        'summary',
        'ConfirmationForm',
    ];

    public function summary(): HTTPResponse|array
    {
        if ($redirect = $this->redirectToFirstIncompleteCheckoutStep()) {
            return $redirect;
        }

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

        $paymentForm = SummaryPaymentForm::create($this->getOwner(), 'ConfirmationForm', $checkoutComponentConfig);
        $paymentForm->setFailureLink($this->getOwner()->Link('summary'));

        $this->getOwner()->extend('updateConfirmationForm', $paymentForm);

        return $paymentForm;
    }

    public function getFirstIncompleteCheckoutStep(): ?string
    {
        $order = ShoppingCart::curr();
        if (!$order instanceof Order) {
            return null;
        }

        $steps = $this->getOwner()->getSteps();
        $checkout = Checkout::get($order);
        if (isset($steps['membership']) && $checkout && !$checkout->validateMember(Security::getCurrentUser())) {
            return 'membership';
        }

        if (isset($steps['contactdetails']) && !$this->hasContactDetails($order)) {
            return 'contactdetails';
        }

        if (isset($steps['shippingaddress']) && !$this->hasValidAddress($order->ShippingAddressID, $order->ShippingAddress())) {
            return 'shippingaddress';
        }

        if (isset($steps['billingaddress']) && !$this->hasValidAddress($order->BillingAddressID, $order->BillingAddress())) {
            return 'billingaddress';
        }

        if (isset($steps['paymentmethod']) && $checkout && !$checkout->getSelectedPaymentMethod()) {
            return 'paymentmethod';
        }

        return null;
    }

    public function getFirstIncompleteCheckoutStepLink(): ?string
    {
        $step = $this->getFirstIncompleteCheckoutStep();

        if (!$step) {
            return null;
        }

        return $this->getOwner()->Link($step);
    }

    protected function redirectToFirstIncompleteCheckoutStep(): ?HTTPResponse
    {
        $link = $this->getFirstIncompleteCheckoutStepLink();
        if (!$link) {
            return null;
        }

        return $this->getOwner()->redirect($link);
    }

    protected function hasContactDetails(Order $order): bool
    {
        return !empty($order->FirstName) && !empty($order->Surname) && !empty($order->Email);
    }

    protected function hasValidAddress(int $addressID, Address $address): bool
    {
        return $addressID > 0 && $address->exists() && $address->validate()->isValid();
    }
}

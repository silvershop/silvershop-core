<?php

declare(strict_types=1);

namespace SilverShop\Checkout\Step;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\Notes;
use SilverShop\Checkout\Component\Terms;
use SilverShop\Forms\PaymentForm;
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

        $paymentForm = PaymentForm::create($this->getOwner(), 'ConfirmationForm', $checkoutComponentConfig);
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
        $firstIncompleteStep = null;
        if (isset($steps['membership']) && $checkout && !$checkout->validateMember(Security::getCurrentUser())) {
            $firstIncompleteStep = 'membership';
        }

        if (!$firstIncompleteStep && isset($steps['contactdetails']) && !$this->hasContactDetails($order)) {
            $firstIncompleteStep = 'contactdetails';
        }

        if (
            !$firstIncompleteStep
            && isset($steps['shippingaddress'])
            && !$this->hasValidAddress($order->ShippingAddressID, $order->ShippingAddress())
        ) {
            $firstIncompleteStep = 'shippingaddress';
        }

        if (
            !$firstIncompleteStep
            && isset($steps['billingaddress'])
            && !$this->hasValidAddress($order->BillingAddressID, $order->BillingAddress())
        ) {
            $firstIncompleteStep = 'billingaddress';
        }

        if (!$firstIncompleteStep && isset($steps['paymentmethod']) && $checkout && !$checkout->getSelectedPaymentMethod()) {
            $firstIncompleteStep = 'paymentmethod';
        }

        $this->getOwner()->extend('updateFirstIncompleteCheckoutStep', $firstIncompleteStep, $order, $steps, $checkout);

        return $firstIncompleteStep;
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
        $hasContactDetails = !empty($order->FirstName) && !empty($order->Surname) && !empty($order->Email);
        $this->getOwner()->extend('updateHasContactDetails', $hasContactDetails, $order);

        return $hasContactDetails;
    }

    protected function hasValidAddress(int $addressID, Address $address): bool
    {
        return $address->exists() && $addressID > 0 && $address->validate()->isValid();
    }
}

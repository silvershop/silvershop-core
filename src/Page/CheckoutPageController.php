<?php

namespace SilverShop\Page;

use SilverShop\Extension\OrderManipulationExtension;
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
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Omnipay\Model\Message\GatewayErrorMessage;
use SilverStripe\View\SSViewer;

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
 * @mixin OrderManipulationExtension
 */
class CheckoutPageController extends PageController
{
    private static string $url_segment     = 'checkout';

    private static array $allowed_actions = [
        'OrderForm',
        'payment',
        'PaymentForm',
    ];

    private static array $steps = [];

    private static string $first_step = '';

    public function Title(): string
    {
        if ($this->failover && $this->failover->Title) {
            return $this->failover->Title;
        }

        return _t('SilverShop\Page\CheckoutPage.DefaultTitle', "Checkout");
    }

    public function OrderForm(): PaymentForm|bool
    {
        if (!(bool)$this->Cart()) {
            return false;
        }

        /**
         * @var CheckoutComponentConfig $singlePageCheckoutComponentConfig
         */
        $singlePageCheckoutComponentConfig = SinglePageCheckoutComponentConfig::create(ShoppingCart::curr());
        $paymentForm = PaymentForm::create($this, 'OrderForm', $singlePageCheckoutComponentConfig);

        // Normally, the payment is on a second page, either offsite or through /checkout/payment
        // If the site has customised the checkout component config to include an onsite payment
        // component, we should honor that and change the button label. PaymentForm::checkoutSubmit
        // will also check this and process payment if needed.
        if ($singlePageCheckoutComponentConfig->hasComponentWithPaymentData()) {
            $paymentForm->setActions(
                FieldList::create(
                    FormAction::create('checkoutSubmit', _t('SilverShop\Page\CheckoutPage.SubmitPayment', 'Submit Payment'))
                )
            );
        }

        $paymentForm->Cart = $this->Cart();
        $this->extend('updateOrderForm', $paymentForm);

        return $paymentForm;
    }

    /**
     * Action for making on-site payments
     */
    public function payment(): HTTPResponse|array
    {
        if (!$this->Cart()) {
            return $this->redirect($this->Link());
        }

        return [
            'Title'     => 'Make Payment',
            'OrderForm' => $this->PaymentForm(),
        ];
    }

    public function PaymentForm(): false|PaymentForm
    {
        if (!(bool)$this->Cart()) {
            return false;
        }

        $checkoutComponentConfig = CheckoutComponentConfig::create(ShoppingCart::curr(), false);
        $checkoutComponentConfig->addComponent(OnsitePayment::create());

        $paymentForm = PaymentForm::create($this, "PaymentForm", $checkoutComponentConfig);

        $paymentForm->setActions(
            FieldList::create(
                FormAction::create("submitpayment", _t('SilverShop\Page\CheckoutPage.SubmitPayment', "Submit Payment"))
            )
        );

        $paymentForm->setFailureLink($this->Link());
        $this->extend('updatePaymentForm', $paymentForm);

        return $paymentForm;
    }

    /**
     * Retrieves error messages for the latest payment (if existing).
     * This can originate e.g. from an earlier offsite gateway API response.
     */
    public function PaymentErrorMessage(): string|bool
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
        if (!$lastErrorMessage instanceof GatewayErrorMessage) {
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
    public function getViewer($action): SSViewer
    {
        if (CheckoutPage::config()->first_step && $action == 'index') {
            $action = CheckoutPage::config()->first_step;
        }
        return parent::getViewer($action);
    }
}

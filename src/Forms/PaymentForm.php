<?php

namespace SilverShop\Forms;

use SilverShop\Checkout\Checkout;
use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Checkout\Component\CheckoutComponentNamespaced;
use SilverShop\Checkout\Component\OnsitePayment;
use SilverShop\Checkout\OrderProcessor;
use SilverShop\Model\Order;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\GatewayInfo;

class PaymentForm extends CheckoutForm
{
    /**
     * @var string URL to redirect the user to on payment success.
     * Not the same as the "confirm" action in {@link PaymentGatewayController}.
     */
    protected $successlink;

    /**
     * @var string URL to redirect the user to on payment failure.
     * Not the same as the "cancel" action in {@link PaymentGatewayController}.
     */
    protected $failurelink;

    /**
     * @var OrderProcessor
     */
    protected $orderProcessor;

    public function __construct($controller, $name, CheckoutComponentConfig $config)
    {
        parent::__construct($controller, $name, $config);

        $this->orderProcessor = OrderProcessor::create($config->getOrder());
    }

    public function setSuccessLink($link)
    {
        $this->successlink = $link;
    }

    public function getSuccessLink()
    {
        return $this->successlink;
    }

    public function setFailureLink($link)
    {
        $this->failurelink = $link;
    }

    public function getFailureLink()
    {
        return $this->failurelink;
    }

    public function checkoutSubmit($data, $form)
    {
        // form validation has passed by this point, so we can save data
        $this->config->setData($form->getData());
        $order = $this->config->getOrder();
        $gateway = Checkout::get($order)->getSelectedPaymentMethod(false);
        if (GatewayInfo::isOffsite($gateway)
            || GatewayInfo::isManual($gateway)
            || $this->config->hasComponentWithPaymentData()
        ) {
            return $this->submitpayment($data, $form);
        }

        return $this->controller->redirect(
            $this->controller->Link('payment') //assumes CheckoutPage
        );
    }

    /**
     * Behaviour can be overwritten by creating a processPaymentResponse method
     * on the controller owning this form. It takes a Symfony\Component\HttpFoundation\Response argument,
     * and expects an SS_HTTPResponse in return.
     */
    public function submitpayment($data, $form)
    {
        $data = $form->getData();

        $cancelUrl = $this->getFailureLink() ? $this->getFailureLink() : $this->controller->Link();

        $order = $this->config->getOrder();

        // final recalculation, before making payment
        $order->calculate();

        // handle cases where order total is 0. Note that the order will appear
        // as "paid", but without a Payment record attached.
        if ($order->GrandTotal() == 0 && Order::config()->allow_zero_order_total) {
            if (!$this->orderProcessor->placeOrder()) {
                $form->sessionMessage($this->orderProcessor->getError());
                return $this->controller->redirectBack();
            }
            return $this->controller->redirect($this->getSuccessLink());
        }

        // try to place order before payment, if configured
        if (Order::config()->place_before_payment) {
            if (!$this->orderProcessor->placeOrder()) {
                $form->sessionMessage($this->orderProcessor->getError());
                return $this->controller->redirectBack();
            }
            $cancelUrl = $this->orderProcessor->getReturnUrl();
        }

        // if we got here from checkoutSubmit and there's a namespaced Component that provides payment data,
        // we need to strip the inputs down to only the checkout component.
        $components = $this->config->getComponents();
        if ($components->first() instanceof CheckoutComponentNamespaced) {
            foreach ($components as $component) {
                if ($component->Proxy()->providesPaymentData()) {
                    $data = array_merge($data, $component->unnamespaceData($data));
                }
            }
        }

        $gateway = Checkout::get($order)->getSelectedPaymentMethod(false);
        $fieldFactory = new GatewayFieldsFactory($gateway);

        // This is where the payment is actually attempted
        $paymentResponse = $this->orderProcessor->makePayment(
            $gateway,
            $fieldFactory->normalizeFormData($data),
            $this->getSuccessLink(),
            $cancelUrl
        );

        $response = null;
        if ($this->controller->hasMethod('processPaymentResponse')) {
            $response = $this->controller->processPaymentResponse($paymentResponse, $form);
        } elseif ($paymentResponse && !$paymentResponse->isError()) {
            $response = $paymentResponse->redirectOrRespond();
        } else {
            $form->sessionMessage($this->orderProcessor->getError(), 'bad');
            $response = $this->controller->redirectBack();
        }

        return $response;
    }

    /**
     * @param OrderProcessor $processor
     */
    public function setOrderProcessor(OrderProcessor $processor)
    {
        $this->orderProcessor = $processor;
    }

    /**
     * @return OrderProcessor
     */
    public function getOrderProcessor()
    {
        return $this->orderProcessor;
    }
}

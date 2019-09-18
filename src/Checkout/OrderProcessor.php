<?php

namespace SilverShop\Checkout;

use ErrorException;
use Exception;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\OrderManipulationExtension;
use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Model\Order;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SilverStripe\Omnipay\Service\ServiceResponse;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Handles tasks to be performed on orders, particularly placing and processing/fulfilment.
 * Placing, Emailing Reciepts, Status Updates, Printing, Payments - things you do with a completed order.
 *
 * @package shop
 */
class OrderProcessor
{
    use Injectable;
    use Configurable;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var OrderEmailNotifier
     */
    protected $notifier;

    /**
     * @var string
     */
    protected $error;


    /**
     * Assign the order to a local variable
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->notifier = OrderEmailNotifier::create($order);
    }

    /**
     * URL to display success message to the user.
     * Happens after any potential offsite gateway redirects.
     *
     * @return String Relative URL
     */
    public function getReturnUrl()
    {
        return $this->order->Link();
    }

    /**
     * Create a payment model, and provide link to redirect to external gateway,
     * or redirect to order link.
     *
     * @param string $gateway the gateway to use
     * @param array $gatewaydata the data that should be passed to the gateway
     * @param string $successUrl (optional) return URL for successful payments.
     *                            If left blank, the default return URL will be
     *                            used @see getReturnUrl
     * @param string $cancelUrl (optional) return URL for cancelled/failed payments
     *
     * @return ServiceResponse|null
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    public function makePayment($gateway, $gatewaydata = array(), $successUrl = null, $cancelUrl = null)
    {
        //create payment
        $payment = $this->createPayment($gateway);
        if (!$payment) {
            //errors have been stored.
            return null;
        }

        $payment->setSuccessUrl($successUrl ? $successUrl : $this->getReturnUrl());

        // Explicitly set the cancel URL
        if ($cancelUrl) {
            $payment->setFailureUrl($cancelUrl);
        }

        // Create a payment service, by using the Service Factory. This will automatically choose an
        // AuthorizeService or PurchaseService, depending on Gateway configuration.
        // Set the user-facing success URL for redirects
        /**
         * @var ServiceFactory $factory
         */
        $factory = ServiceFactory::create();
        $service = $factory->getService($payment, ServiceFactory::INTENT_PAYMENT);

        // Initiate payment, get the result back
        try {
            $serviceResponse = $service->initiate($this->getGatewayData($gatewaydata));
        } catch (\SilverStripe\Omnipay\Exception\Exception $ex) {
            // error out when an exception occurs
            $this->error($ex->getMessage());
            return null;
        }

        // Check if the service response itself contains an error
        if ($serviceResponse->isError()) {
            if ($opResponse = $serviceResponse->getOmnipayResponse()) {
                $this->error($opResponse->getMessage());
            } else {
                $this->error('An unspecified payment error occurred. Please check the payment messages.');
            }
        }

        // For an OFFSITE payment, serviceResponse will now contain a redirect
        // For an ONSITE payment, ShopPayment::onCaptured will have been called, which will have called completePayment

        return $serviceResponse;
    }

    /**
     * Map shop data to omnipay fields
     *
     * @param array $customData Usually user submitted data.
     *
     * @return array
     */
    protected function getGatewayData($customData)
    {
        $shipping = $this->order->getShippingAddress();
        $billing = $this->order->getBillingAddress();

        $numPayments = Payment::get()
            ->filter(array('OrderID' => $this->order->ID))
            ->count() - 1;

        $transactionId = $this->order->Reference . ($numPayments > 0 ? "-$numPayments" : '');

        return array_merge(
            $customData,
            array(
                'transactionId'    => $transactionId,
                'firstName'        => $this->order->FirstName,
                'lastName'         => $this->order->Surname,
                'email'            => $this->order->Email,
                'company'          => $this->order->Company,
                'billingAddress1'  => $billing->Address,
                'billingAddress2'  => $billing->AddressLine2,
                'billingCity'      => $billing->City,
                'billingPostcode'  => $billing->PostalCode,
                'billingState'     => $billing->State,
                'billingCountry'   => $billing->Country,
                'billingPhone'     => $billing->Phone,
                'shippingAddress1' => $shipping->Address,
                'shippingAddress2' => $shipping->AddressLine2,
                'shippingCity'     => $shipping->City,
                'shippingPostcode' => $shipping->PostalCode,
                'shippingState'    => $shipping->State,
                'shippingCountry'  => $shipping->Country,
                'shippingPhone'    => $shipping->Phone,
            )
        );
    }

    /**
     * Create a new payment for an order
     */
    public function createPayment($gateway)
    {
        if (!GatewayInfo::isSupported($gateway)) {
            $this->error(
                _t(
                    __CLASS__ . ".InvalidGateway",
                    "`{gateway}` isn't a valid payment gateway.",
                    'gateway is the name of the payment gateway',
                    array('gateway' => $gateway)
                )
            );
            return false;
        }
        if (!$this->order->canPay(Security::getCurrentUser())) {
            $this->error(_t(__CLASS__ . ".CantPay", "Order can't be paid for."));
            return false;
        }
        $payment = Payment::create()->init(
            $gateway,
            $this->order->TotalOutstanding(true),
            ShopConfigExtension::config()->base_currency
        );
        $this->order->Payments()->add($payment);
        return $payment;
    }

    /**
     * Complete payment processing
     *    - send receipt
     *    - update order status accordingling
     *    - fire event hooks
     */
    public function completePayment()
    {
        if (!$this->order->IsPaid()) {

            $this->order->extend('onPayment'); //a payment has been made
            //place the order, if not already placed
            if ($this->canPlace($this->order)) {
                $this->placeOrder();
            } else {
                if ($this->order->Locale) {
                    ShopTools::install_locale($this->order->Locale);
                }
            }

            if (($this->order->GrandTotal() > 0 && $this->order->TotalOutstanding(false) <= 0)
                // Zero-dollar order (e.g. paid with loyalty points)
                || ($this->order->GrandTotal() == 0 && Order::config()->allow_zero_order_total)
            ) {
                //set order as paid
                $this->order->Status = 'Paid';
                $this->order->write();
            }
        }
    }

    /**
     * Determine if an order can be placed.
     *
     * @param boolean $order
     */
    public function canPlace(Order $order)
    {
        if (!$order) {
            $this->error(_t(__CLASS__ . ".NoOrder", "Order does not exist."));
            return false;
        }
        //order status is applicable
        if (!$order->IsCart()) {
            $this->error(_t(__CLASS__ . ".NotCart", "Order is not a cart."));
            return false;
        }
        //order has products
        if ($order->Items()->Count() <= 0) {
            $this->error(_t(__CLASS__ . ".NoItems", "Order has no items."));
            return false;
        }

        return true;
    }

    /**
     * Takes an order from being a cart to awaiting payment.
     *
     * @return boolean - success/failure
     */
    public function placeOrder()
    {
        if (!$this->order) {
            $this->error(_t(__CLASS__ . ".NoOrderStarted", "A new order has not yet been started."));
            return false;
        }
        if (!$this->canPlace($this->order)) { //final cart validation
            return false;
        }

        if ($this->order->Locale) {
            ShopTools::install_locale($this->order->Locale);
        }

        if (DB::get_conn()->supportsTransactions()) {
            DB::get_conn()->transactionStart();
        }

        //update status
        if ($this->order->TotalOutstanding(false)) {
            $this->order->Status = 'Unpaid';
        } else {
            $this->order->Status = 'Paid';
        }

        if (!$this->order->Placed) {
            $this->order->Placed = DBDatetime::now()->Rfc2822(); //record placed order datetime
            if ($request = Controller::curr()->getRequest()) {
                $this->order->IPAddress = $request->getIP(); //record client IP
            }
        }

        // Add an error handler that throws an exception upon error, so that we can catch errors as exceptions
        // in the following block.
        set_error_handler(
            function ($severity, $message, $file, $line) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            },
            E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED)
        );

        try {
            //re-write all attributes and modifiers to make sure they are up-to-date before they can't be changed again
            $items = $this->order->Items();
            if ($items->exists()) {
                foreach ($items as $item) {
                    $item->onPlacement();
                    $item->write();
                }
            }
            $modifiers = $this->order->Modifiers();
            if ($modifiers->exists()) {
                foreach ($modifiers as $modifier) {
                    $modifier->write();
                }
            }
            //add member to order & customers group
            if ($member = Security::getCurrentUser()) {
                if (!$this->order->MemberID) {
                    $this->order->MemberID = $member->ID;
                }
                $cgroup = ShopConfigExtension::current()->CustomerGroup();
                if ($cgroup->exists()) {
                    $member->Groups()->add($cgroup);
                }
            }
            //allow decorators to do stuff when order is saved.
            $this->order->extend('onPlaceOrder');
            $this->order->write();
        } catch (Exception $ex) {
            // Rollback the transaction if an error occurred
            if (DB::get_conn()->supportsTransactions()) {
                DB::get_conn()->transactionRollback();
            }
            $this->error($ex->getMessage());
            return false;
        } finally {
            // restore the error handler, no matter what
            restore_error_handler();
        }

        // Everything went through fine, complete the transaction
        if (DB::get_conn()->supportsTransactions()) {
            DB::get_conn()->transactionEnd();
        }

        //remove from session
        ShoppingCart::singleton()->clear(false);
        /*
        $cart = ShoppingCart::curr();
        if ($cart && $cart->ID == $this->order->ID) {
            // clear the cart, but don't write the order in the process (order is finalized and should NOT be overwritten)
        }
        */

        //send confirmation if configured and receipt hasn't been sent
        if (self::config()->send_confirmation
            && !$this->order->ReceiptSent
        ) {
            $this->notifier->sendConfirmation();
        }

        //notify admin, if configured
        if (self::config()->send_admin_notification) {
            $this->notifier->sendAdminNotification();
        }

        // Save order reference to session
        OrderManipulationExtension::add_session_order($this->order);

        return true; //report success
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function error($message)
    {
        $this->error = $message;
    }
}

<?php

namespace SilverShop\Checkout;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverShop\ShopTools;
use SilverShop\ShopUserInfo;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Helper class for getting an order throught the checkout process
 */
class Checkout
{
    use Injectable;

    /**
     * 4 different membership schemes:
     *    1: creation disabled & membership not required
     *        no body can, or is required to, become a member at checkout.
     *    2: creation disabled & membership required
     *        only existing members can use checkout.
     *        (ideally the entire shop should be disabled in this case)
     *    3: creation enabled & membership required
     *        everyone must be, or become a member at checkout.
     *    4: creation enabled & membership not required (default)
     *        it is optional to be, or become a member at checkout.
     */

    public static function member_creation_enabled()
    {
        return CheckoutConfig::config()->member_creation_enabled;
    }

    public static function membership_required()
    {
        return CheckoutConfig::config()->membership_required;
    }

    public static function get($order = null)
    {
        if ($order === null) {
            $order = ShoppingCart::curr(); //roll back to current cart
        }
        if ($order->exists() && $order->isInDB()) {//check if order can go through checkout
            return Checkout::create($order);
        }
        return false;
    }

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $type;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get stored message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get type of stored message
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->type;
    }

    /**
     * contact information
     */
    public function setContactDetails($email, $firstname, $surname)
    {
        $this->order->Email = $email;
        $this->order->FirstName = $firstname;
        $this->order->Surname = $surname;
        $this->order->write();
    }

    //save / set up addresses
    public function setShippingAddress(Address $address)
    {
        $this->order->ShippingAddressID = $address->ID;
        if ($member = Security::getCurrentUser()) {
            $this->order->MemberID = $member->ID;
        }
        $this->order->write();
        $this->order->extend('onSetShippingAddress', $address);

        ShopUserInfo::singleton()->setAddress($address);
    }

    public function setBillingAddress(Address $address)
    {
        $this->order->BillingAddressID = $address->ID;
        if ($member = Security::getCurrentUser()) {
            $this->order->MemberID = $member->ID;
        }
        $this->order->write();
        $this->order->extend('onSetBillingAddress', $address);
    }

    /**
     * Set payment method
     *
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    public function setPaymentMethod($paymentmethod)
    {
        $methods = GatewayInfo::getSupportedGateways();
        if (!isset($methods[$paymentmethod])) {
            ShopTools::getSession()
                ->set('Checkout.PaymentMethod', null)
                ->clear('Checkout.PaymentMethod');
            return $this->error(_t(__CLASS__ . '.NoPaymentMethod', 'Payment method does not exist'));
        }
        ShopTools::getSession()->set('Checkout.PaymentMethod', $paymentmethod);
        return true;
    }

    /**
     * Gets the selected payment method from the session,
     * or the only available method, if there is only one.
     *
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    public function getSelectedPaymentMethod($nice = false)
    {
        $methods = GatewayInfo::getSupportedGateways();
        reset($methods);
        $method = count($methods) === 1 ? key($methods) : ShopTools::getSession()->get('Checkout.PaymentMethod');
        if ($nice && isset($methods[$method])) {
            $method = $methods[$method];
        }
        return $method;
    }

    /**
     * Checks if member (or not) is allowed, in accordance with configuration
     */
    public function validateMember($member)
    {
        if (!CheckoutConfig::config()->membership_required) {
            return true;
        }
        if (empty($member) || !($member instanceof Member)) {
            return false;
        }

        return true;
    }

    /**
     * Store a new error & return false;
     */
    protected function error($message)
    {
        $this->message($message, 'bad');
        return false;
    }

    /**
     * Store a message to be fed back to user.
     *
     * @param string $message
     * @param string $type    - good, bad, warning
     */
    protected function message($message, $type = 'good')
    {
        $this->message = $message;
        $this->type = $type;
    }
}

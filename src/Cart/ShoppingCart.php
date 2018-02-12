<?php

namespace SilverShop\Cart;

use Exception;
use SilverShop\Extension\OrderManipulationExtension;
use SilverShop\Extension\ProductVariationsExtension;
use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Model\OrderItem;
use SilverShop\ORM\Filters\MatchObjectFilter;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Encapsulated manipulation of the current order using a singleton pattern.
 *
 * Ensures that an order is only started (persisted to DB) when necessary,
 * and all future changes are on the same order, until the order has is placed.
 * The requirement for starting an order is to adding an item to the cart.
 *
 * @package shop
 */
class ShoppingCart
{
    use Injectable;
    use Configurable;

    private static $cartid_session_name = 'SilverShop.shoppingcartid';

    /**
     * @var Order
     */
    private $order;

    private $calculateonce = false;

    private $message;

    private $type;


    /**
     * Shortened alias for ShoppingCart::singleton()->current()
     *
     * @return Order
     */
    public static function curr()
    {
        return self::singleton()->current();
    }

    /**
     * Get the current order, or return null if it doesn't exist.
     *
     * @return Order
     */
    public function current()
    {
        $session = ShopTools::getSession();
        //find order by id saved to session (allows logging out and retaining cart contents)
        if (!$this->order && $sessionid = $session->get(self::config()->cartid_session_name)) {
            $this->order = Order::get()->filter(
                [
                    'Status' => 'Cart',
                    'ID' => $sessionid,
                ]
            )->first();
        }
        if (!$this->calculateonce && $this->order) {
            $this->order->calculate();
            $this->calculateonce = true;
        }

        return $this->order ? $this->order : null;
    }

    /**
     * Set the current cart
     *
     * @param Order $cart the Order to use as the current cart-content
     *
     * @return ShoppingCart
     */
    public function setCurrent(Order $cart)
    {
        if (!$cart->IsCart()) {
            trigger_error('Passed Order object is not cart status', E_ERROR);
        }
        $this->order = $cart;
        $session = ShopTools::getSession();
        $session->set(self::config()->cartid_session_name, $cart->ID);

        return $this;
    }

    /**
     * Helper that only allows orders to be started internally.
     *
     * @return Order
     */
    protected function findOrMake()
    {
        if ($this->current()) {
            return $this->current();
        }
        $this->order = Order::create();
        if (Member::config()->login_joins_cart && ($member = Security::getCurrentUser())) {
            $this->order->MemberID = $member->ID;
        }
        $this->order->write();
        $this->order->extend('onStartOrder');

        $session = ShopTools::getSession();
        $session->set(self::config()->cartid_session_name, $this->order->ID);

        return $this->order;
    }

    /**
     * Adds an item to the cart
     *
     * @param Buyable $buyable
     * @param int     $quantity
     * @param array   $filter
     *
     * @return boolean|OrderItem false or the new/existing item
     */
    public function add(Buyable $buyable, $quantity = 1, $filter = [])
    {
        $order = $this->findOrMake();

        // If an extension throws an exception, error out
        try {
            $order->extend('beforeAdd', $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        if (!$buyable) {
            return $this->error(_t(__CLASS__ . '.ProductNotFound', 'Product not found.'));
        }

        $item = $this->findOrMakeItem($buyable, $quantity, $filter);
        if (!$item) {
            return false;
        }
        if (!$item->_brandnew) {
            $item->Quantity += $quantity;
        } else {
            $item->Quantity = $quantity;
        }

        // If an extension throws an exception, error out
        try {
            $order->extend('afterAdd', $item, $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $item->write();
        $this->message(_t(__CLASS__ . '.ItemAdded', 'Item has been added successfully.'));

        return $item;
    }

    /**
     * Remove an item from the cart.
     *
     * @param Buyable $buyable
     * @param int     $quantity - number of items to remove, or leave null for all items (default)
     * @param array   $filter
     *
     * @return boolean success/failure
     */
    public function remove(Buyable $buyable, $quantity = null, $filter = [])
    {
        $order = $this->current();

        if (!$order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        // If an extension throws an exception, error out
        try {
            $order->extend('beforeRemove', $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $item = $this->get($buyable, $filter);

        if (!$item || !$this->removeOrderItem($item, $quantity)) {
            return false;
        }

        // If an extension throws an exception, error out
        // TODO: There should be a rollback
        try {
            $order->extend('afterRemove', $item, $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $this->message(_t(__CLASS__ . '.ItemRemoved', 'Item has been successfully removed.'));

        return true;
    }

    /**
     * Remove a specific order item from cart
     *
     * @param  OrderItem $item
     * @param  int       $quantity - number of items to remove or leave `null` to remove all items (default)
     * @return boolean success/failure
     */
    public function removeOrderItem(OrderItem $item, $quantity = null)
    {
        $order = $this->current();

        if (!$order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if (!$item || $item->OrderID != $order->ID) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        //if $quantity will become 0, then remove all
        if (!$quantity || ($item->Quantity - $quantity) <= 0) {
            $item->delete();
            $item->destroy();
        } else {
            $item->Quantity -= $quantity;
            $item->write();
        }

        return true;
    }

    /**
     * Sets the quantity of an item in the cart.
     * Will automatically add or remove item, if necessary.
     *
     * @param Buyable $buyable
     * @param int     $quantity
     * @param array   $filter
     *
     * @return boolean|OrderItem false or the new/existing item
     */
    public function setQuantity(Buyable $buyable, $quantity = 1, $filter = [])
    {
        if ($quantity <= 0) {
            return $this->remove($buyable, $quantity, $filter);
        }

        $item = $this->findOrMakeItem($buyable, $quantity, $filter);

        if (!$item || !$this->updateOrderItemQuantity($item, $quantity, $filter)) {
            return false;
        }

        return $item;
    }

    /**
     * Update quantity of a given order item
     *
     * @param  OrderItem $item
     * @param  int       $quantity the new quantity to use
     * @param  array     $filter
     * @return boolean success/failure
     */
    public function updateOrderItemQuantity(OrderItem $item, $quantity = 1, $filter = [])
    {
        $order = $this->current();

        if (!$order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if (!$item || $item->OrderID != $order->ID) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        $buyable = $item->Buyable();
        // If an extension throws an exception, error out
        try {
            $order->extend('beforeSetQuantity', $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $item->Quantity = $quantity;

        // If an extension throws an exception, error out
        try {
            $order->extend('afterSetQuantity', $item, $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $item->write();
        $this->message(_t(__CLASS__ . '.QuantitySet', 'Quantity has been set.'));

        return true;
    }

    /**
     * Finds or makes an order item for a given product + filter.
     *
     * @param Buyable $buyable  the buyable
     * @param int     $quantity quantity to add
     * @param array   $filter
     *
     * @return OrderItem the found or created item
     * @throws \SilverStripe\ORM\ValidationException
     */
    private function findOrMakeItem(Buyable $buyable, $quantity = 1, $filter = [])
    {
        $order = $this->findOrMake();

        if (!$buyable || !$order) {
            return null;
        }

        $item = $this->get($buyable, $filter);

        if (!$item) {
            $member = Security::getCurrentUser();

            $buyable = $this->getCorrectBuyable($buyable);

            if (!$buyable->canPurchase($member, $quantity)) {
                return $this->error(
                    _t(
                        __CLASS__ . '.CannotPurchase',
                        'This {Title} cannot be purchased.',
                        '',
                        ['Title' => $buyable->i18n_singular_name()]
                    )
                );
                //TODO: produce a more specific message
            }

            $item = $buyable->createItem($quantity, $filter);
            $item->OrderID = $order->ID;
            $item->write();

            $order->Items()->add($item);

            $item->_brandnew = true; // flag as being new
        }

        return $item;
    }

    /**
     * Finds an existing order item.
     *
     * @param Buyable $buyable
     * @param array   $customfilter
     *
     * @return OrderItem the item requested or null
     */
    public function get(Buyable $buyable, $customfilter = array())
    {
        $order = $this->current();
        if (!$buyable || !$order) {
            return null;
        }

        $buyable = $this->getCorrectBuyable($buyable);

        $filter = array(
            'OrderID' => $order->ID,
        );

        $itemclass = Config::inst()->get(get_class($buyable), 'order_item');
        $relationship = Config::inst()->get($itemclass, 'buyable_relationship');
        $filter[$relationship . 'ID'] = $buyable->ID;
        $required = ['OrderID', $relationship . 'ID'];
        if (is_array($itemclass::config()->required_fields)) {
            $required = array_merge($required, $itemclass::config()->required_fields);
        }
        $query = new MatchObjectFilter($itemclass, array_merge($customfilter, $filter), $required);
        $item = $itemclass::get()->where($query->getFilter())->first();
        if (!$item) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        return $item;
    }

    /**
     * Ensure the proper buyable will be returned for a given buyable…
     * This is being used to ensure a product with variations cannot be added to the cart…
     * a Variation has to be added instead!
     *
     * @param  Buyable $buyable
     * @return Buyable
     */
    public function getCorrectBuyable(Buyable $buyable)
    {
        if ($buyable instanceof Product
            && $buyable->hasExtension(ProductVariationsExtension::class)
            && $buyable->Variations()->count() > 0
        ) {
            foreach ($buyable->Variations() as $variation) {
                if ($variation->canPurchase()) {
                    return $variation;
                }
            }
        }

        return $buyable;
    }

    /**
     * Store old cart id in session order history
     *
     * @param int|null $requestedOrderId optional parameter that denotes the order that was requested
     */
    public function archiveorderid($requestedOrderId = null)
    {
        $session = ShopTools::getSession();
        $sessionId = $session->get(self::config()->cartid_session_name);
        $order = Order::get()
            ->filter('Status:not', 'Cart')
            ->byId($sessionId);

        if ($order && !$order->IsCart()) {
            OrderManipulationExtension::add_session_order($order);
        }
        // in case there was no order requested
        // OR there was an order requested AND it's the same one as currently in the session,
        // then clear the cart. This check is here to prevent clearing of the cart if the user just
        // wants to view an old order (via AccountPage).
        if (!$requestedOrderId || ($sessionId == $requestedOrderId)) {
            $this->clear();
        }
    }

    /**
     * Empty / abandon the entire cart.
     *
     * @param  bool $write whether or not to write the abandoned order
     * @return bool - true if successful, false if no cart found
     */
    public function clear($write = true)
    {
        $session = ShopTools::getSession();
        $session->set(self::config()->cartid_session_name, null)->clear(self::config()->cartid_session_name);
        $order = $this->current();
        $this->order = null;

        if ($write) {
            if (!$order) {
                return $this->error(_t(__CLASS__ . '.NoCartFound', 'No cart found.'));
            }
            $order->write();
        }
        $this->message(_t(__CLASS__ . '.Cleared', 'Cart was successfully cleared.'));

        return true;
    }

    /**
     * Store a new error.
     */
    protected function error($message)
    {
        $this->message($message, 'bad');

        return null;
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

    public function getMessage()
    {
        return $this->message;
    }

    public function getMessageType()
    {
        return $this->type;
    }

    public function clearMessage()
    {
        $this->message = null;
    }

    //singleton protection
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }
}

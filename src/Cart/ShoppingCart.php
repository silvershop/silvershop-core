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
use SilverStripe\ORM\ValidationException;
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

    private static string $cartid_session_name = 'SilverShop.shoppingcartid';

    private $order;
    private bool $calculateonce = false;
    private string $message = '';
    private string $type = '';

    /**
     * Shortened alias for ShoppingCart::singleton()->current()
     */
    public static function curr(): ?Order
    {
        return self::singleton()->current();
    }

    /**
     * Get the current order, or return null if it doesn't exist.
     */
    public function current(): ?Order
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
     * @param Order $order the current cart-content
     */
    public function setCurrent(Order $order): static
    {
        if (!$order->IsCart()) {
            trigger_error('Passed Order object is not cart status', E_ERROR);
        }
        $this->order = $order;
        $session = ShopTools::getSession();
        $session->set(self::config()->cartid_session_name, $order->ID);

        return $this;
    }

    /**
     * Helper that only allows orders to be started internally.
     */
    protected function findOrMake(): Order
    {
        if ($this->current() instanceof Order) {
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
     * @param int $quantity
     * @param array $filter
     * @return bool|null|OrderItem false or the new/existing item
     */
    public function add(Buyable $buyable, $quantity = 1, $filter = []): bool|null|OrderItem
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

        if (!$item instanceof OrderItem) {
            return false;
        }

        if (!$item->brandNew) {
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
     * @param int $quantity - number of items to remove, or leave null for all items (default)
     * @param array $filter
     * @return boolean success/failure
     */
    public function remove(Buyable $buyable, $quantity = null, $filter = []): ?bool
    {
        $order = $this->current();

        if (!$order instanceof Order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        // If an extension throws an exception, error out
        try {
            $order->extend('beforeRemove', $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $item = $this->get($buyable, $filter);

        if (!$item instanceof OrderItem || $this->removeOrderItem($item, $quantity) !== true) {
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
     * @param int $quantity - number of items to remove or leave `null` to remove all items (default)
     * @return ?bool success/failure
     */
    public function removeOrderItem(OrderItem $orderItem, $quantity = null): ?bool
    {
        $order = $this->current();

        if (!$order instanceof Order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if ($orderItem->OrderID != $order->ID) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        //if $quantity will become 0, then remove all
        if (!$quantity || ($orderItem->Quantity - $quantity) <= 0) {
            $orderItem->delete();
            $orderItem->destroy();
        } else {
            $orderItem->Quantity -= $quantity;
            $orderItem->write();
        }

        return true;
    }

    /**
     * Sets the quantity of an item in the cart.
     * Will automatically add or remove item, if necessary.
     *
     * @param int $quantity
     * @param array $filter
     * @return bool|null|OrderItem false or the new/existing item
     */
    public function setQuantity(Buyable $buyable, $quantity = 1, $filter = []): bool|null|OrderItem
    {
        if ($quantity <= 0) {
            return $this->remove($buyable, $quantity, $filter);
        }

        $item = $this->findOrMakeItem($buyable, $quantity, $filter);

        if (!$item instanceof OrderItem || $this->updateOrderItemQuantity($item, $quantity, $filter) !== true) {
            return false;
        }

        return $item;
    }

    /**
     * Update quantity of a given order item
     *
     * @param int $quantity the new quantity to use
     * @param array $filter
     * @return ?bool success/failure
     */
    public function updateOrderItemQuantity(OrderItem $orderItem, $quantity = 1, $filter = []): ?bool
    {
        $order = $this->current();

        if (!$order instanceof Order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if ($orderItem->OrderID != $order->ID) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        $buyable = $orderItem->Buyable();
        // If an extension throws an exception, error out
        try {
            $order->extend('beforeSetQuantity', $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $orderItem->Quantity = $quantity;

        // If an extension throws an exception, error out
        try {
            $order->extend('afterSetQuantity', $orderItem, $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $orderItem->write();
        $this->message(_t(__CLASS__ . '.QuantitySet', 'Quantity has been set.'));

        return true;
    }

    /**
     * Finds or makes an order item for a given product + filter.
     *
     * @param Buyable $buyable  the buyable
     * @param int $quantity quantity to add
     * @param array $filter
     * @throws ValidationException
     */
    private function findOrMakeItem(Buyable $buyable, $quantity = 1, $filter = []): ?OrderItem
    {
        $order = $this->findOrMake();
        $item = $this->get($buyable, $filter);

        if (!$item instanceof OrderItem) {
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
            $item->brandNew = true;
        }

        return $item;
    }

    /**
     * Finds an existing order item.
     *
     * @param array $customFilter
     * @return ?OrderItem the item requested or null
     */
    public function get(Buyable $buyable, $customFilter = []): ?OrderItem
    {
        $order = $this->current();

        if (!$order instanceof Order) {
            return null;
        }

        $buyable = $this->getCorrectBuyable($buyable);

        $filter = [
            'OrderID' => $order->ID,
        ];

        $itemClass = Config::inst()->get(get_class($buyable), 'order_item');

        if (!$itemClass) {
            $itemClass = OrderItem::class;
        }

        $relationship = Config::inst()->get($itemClass, 'buyable_relationship');
        $filter[$relationship . 'ID'] = $buyable->ID;
        $required = ['OrderID', $relationship . 'ID'];

        if (is_array($itemClass::config()->required_fields)) {
            $required = array_merge($required, $itemClass::config()->required_fields);
        }

        $matchObjectFilter = new MatchObjectFilter($itemClass, array_merge($customFilter, $filter), $required);

        $item = $itemClass::get()->where($matchObjectFilter->getFilter())->first();

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
     * @return Buyable
     */
    public function getCorrectBuyable(Buyable $buyable)
    {
        if ($buyable instanceof Product
            && $buyable->hasExtension(ProductVariationsExtension::class)
            && $buyable->Variations()->count() > 0
        ) {
            foreach ($buyable->Variations() as $hasManyList) {
                if ($hasManyList->canPurchase()) {
                    return $hasManyList;
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
    public function archiveorderid($requestedOrderId = null): void
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
     * @return ?bool - true if successful, false if no cart found
     */
    public function clear($write = true): ?bool
    {
        $session = ShopTools::getSession();
        $session->set(self::config()->cartid_session_name, null)->clear(self::config()->cartid_session_name);
        $order = $this->current();
        $this->order = null;

        if ($write) {
            if (!$order instanceof Order) {
                return $this->error(_t(__CLASS__ . '.NoCartFound', 'No cart found.'));
            }
            $order->write();
        }
        $this->message(_t(__CLASS__ . '.Cleared', 'Cart was successfully cleared.'));

        return true;
    }

    /**
     * Store a new error.
     * @return null
     */
    protected function error(string $message)
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
    protected function message(string $message, string $type = 'good'): void
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageType(): string
    {
        return $this->type;
    }

    public function clearMessage(): void
    {
        $this->message = '';
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

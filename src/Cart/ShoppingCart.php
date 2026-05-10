<?php

declare(strict_types=1);

namespace SilverShop\Cart;

use SilverStripe\Core\Validation\ValidationException;
use Exception;
use SilverShop\Currency\CurrencyService;
use SilverShop\Extension\OrderManipulationExtension;
use SilverShop\Extension\ProductVariationsExtension;
use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Model\OrderItem;
use SilverShop\Model\Variation\OrderItem as VariationOrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\ORM\Filters\MatchObjectFilter;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBField;
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

    private DBField|string $message = '';

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
            trigger_error('Passed Order object is not cart status', E_USER_ERROR);
        }

        $this->order = $order;
        $session = ShopTools::getSession();
        $session->set(self::config()->cartid_session_name, $order->ID);

        return $this;
    }

    /**
     * If the singleton has no cart loaded but this line belongs to a cart order, restore session context.
     */
    protected function restoreCartContextFromLine(OrderItem $orderItem): void
    {
        $linked = $orderItem->Order();
        if ($linked && $linked->IsCart()) {
            $this->setCurrent($linked);
        }
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

        // Set the active currency on the order
        $currencyService = Injector::inst()->get(CurrencyService::class);
        $this->order->Currency = $currencyService->getActiveCurrency();

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
        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            return false;
        }

        $order = $this->findOrMake();
        $buyable = $this->getCorrectBuyable($buyable);
        $existing = $this->findLineItem($buyable, $filter);

        $currentQty = $existing ? (int) $existing->Quantity : 0;
        $proposedTotal = $currentQty + $quantity;

        $modOutcome = $this->validateCartItemModification(
            new CartItemModificationContext(
                CartItemModificationContext::OP_ADD,
                $order,
                $existing,
                $buyable,
                $filter,
                $quantity,
                $proposedTotal
            )
        );

        if ($modOutcome->abort) {
            if ($modOutcome->message !== null) {
                $this->message($modOutcome->message, $modOutcome->messageType);
            }

            return null;
        }

        $finalTotal = $modOutcome->lineQuantityAfter ?? $proposedTotal;
        if ($finalTotal < 0) {
            $finalTotal = 0;
        }

        if ($finalTotal === 0) {
            if ($existing instanceof OrderItem) {
                return $this->removeOrderItem($existing, null) ? null : false;
            }

            return false;
        }

        $member = Security::getCurrentUser();

        if (!$buyable->canPurchase($member, $finalTotal)) {
            return $this->error(
                _t(
                    __CLASS__ . '.CannotPurchase',
                    'This {Title} cannot be purchased.',
                    '',
                    ['Title' => $buyable->i18n_singular_name()]
                )
            );
        }

        $effectiveDelta = max(0, $finalTotal - $currentQty);

        try {
            $order->extend('beforeAdd', $buyable, $effectiveDelta, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        if (!$existing instanceof OrderItem) {
            $item = $buyable->createItem($finalTotal, $filter);
            $item->OrderID = $order->ID;
            $item->write();
            $order->Items()->add($item);
            $item->brandNew = true;
        } else {
            $item = $existing;
            $item->Quantity = $finalTotal;
            $item->brandNew = false;
        }

        try {
            $order->extend('afterAdd', $item, $buyable, $effectiveDelta, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $item->write();

        if (!$modOutcome->abort && $modOutcome->message && $modOutcome->messageType !== 'good') {
            $this->message($modOutcome->message, $modOutcome->messageType);
        } else {
            $this->message(_t(__CLASS__ . '.ItemAdded', 'Item has been added successfully.'));
        }

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
            // Nothing in the cart yet — removal is already the desired state (e.g. quantity 0 before first add).
            return true;
        }

        $buyable = $this->getCorrectBuyable($buyable);
        $item = $this->findLineItem($buyable, $filter);

        if (!$item instanceof OrderItem) {
            return false;
        }

        $currentQty = (int) $item->Quantity;
        $removeUnits = $quantity === null ? $currentQty : (int) $quantity;

        if ($removeUnits < 0) {
            return false;
        }

        // Match legacy removeOrderItem behaviour: an explicit 0 means "remove the entire line".
        if ($removeUnits === 0) {
            $removeUnits = $currentQty;
        }

        if ($removeUnits > $currentQty) {
            $removeUnits = $currentQty;
        }

        $proposedRemaining = max(0, $currentQty - $removeUnits);

        $modOutcome = $this->validateCartItemModification(
            new CartItemModificationContext(
                CartItemModificationContext::OP_REMOVE,
                $order,
                $item,
                $buyable,
                $filter,
                $removeUnits,
                $proposedRemaining
            )
        );

        if ($modOutcome->abort) {
            if ($modOutcome->message !== null) {
                $this->message($modOutcome->message, $modOutcome->messageType);
            }

            return null;
        }

        $finalRemaining = $modOutcome->lineQuantityAfter ?? $proposedRemaining;
        if ($finalRemaining < 0) {
            $finalRemaining = 0;
        }

        try {
            $order->extend('beforeRemove', $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        if ($finalRemaining <= 0) {
            $item->delete();
            $item->destroy();
        } else {
            $item->Quantity = $finalRemaining;
            $item->write();
        }

        try {
            $order->extend('afterRemove', $item, $buyable, $quantity, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        if (!$modOutcome->abort && $modOutcome->message && $modOutcome->messageType !== 'good') {
            $this->message($modOutcome->message, $modOutcome->messageType);
        } else {
            $this->message(_t(__CLASS__ . '.ItemRemoved', 'Item has been successfully removed.'));
        }

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
        $this->restoreCartContextFromLine($orderItem);

        $order = $this->current();

        if (!$order instanceof Order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if ($orderItem->OrderID != $order->ID) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        $buyable = $orderItem->Buyable();
        if (!$buyable instanceof Buyable) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        $currentQty = (int) $orderItem->Quantity;
        $removeUnits = $quantity === null ? $currentQty : (int) $quantity;

        if ($removeUnits > $currentQty) {
            $removeUnits = $currentQty;
        }

        $proposedRemaining = max(0, $currentQty - $removeUnits);

        $modOutcome = $this->validateCartItemModification(
            new CartItemModificationContext(
                CartItemModificationContext::OP_REMOVE,
                $order,
                $orderItem,
                $buyable,
                [],
                $removeUnits,
                $proposedRemaining
            )
        );

        if ($modOutcome->abort) {
            if ($modOutcome->message !== null) {
                $this->message($modOutcome->message, $modOutcome->messageType);
            }

            return null;
        }

        $finalRemaining = $modOutcome->lineQuantityAfter ?? $proposedRemaining;
        if ($finalRemaining < 0) {
            $finalRemaining = 0;
        }

        try {
            $order->extend('beforeRemove', $buyable, $quantity, []);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        if ($finalRemaining <= 0) {
            $orderItem->delete();
            $orderItem->destroy();
        } else {
            $orderItem->Quantity = $finalRemaining;
            $orderItem->write();
        }

        try {
            $order->extend('afterRemove', $orderItem, $buyable, $quantity, []);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
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
        $this->restoreCartContextFromLine($orderItem);

        $order = $this->current();

        if (!$order instanceof Order) {
            return $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if ($orderItem->OrderID != $order->ID) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        $buyable = $orderItem->Buyable();
        if (!$buyable instanceof Buyable) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        $requested = (int) $quantity;

        if ($requested <= 0) {
            return $this->removeOrderItem($orderItem, null);
        }

        $modOutcome = $this->validateCartItemModification(
            new CartItemModificationContext(
                CartItemModificationContext::OP_SET_QUANTITY,
                $order,
                $orderItem,
                $buyable,
                $filter,
                $requested,
                $requested
            )
        );

        if ($modOutcome->abort) {
            if ($modOutcome->message !== null) {
                $this->message($modOutcome->message, $modOutcome->messageType);
            }

            return null;
        }

        $finalQty = $modOutcome->lineQuantityAfter ?? $requested;
        if ($finalQty < 0) {
            $finalQty = 0;
        }

        if ($finalQty === 0) {
            return $this->removeOrderItem($orderItem, null);
        }

        $member = Security::getCurrentUser();

        if (!$buyable->canPurchase($member, $finalQty)) {
            return $this->error(
                _t(
                    __CLASS__ . '.CannotPurchase',
                    'This {Title} cannot be purchased.',
                    '',
                    ['Title' => $buyable->i18n_singular_name()]
                )
            );
        }

        try {
            $order->extend('beforeSetQuantity', $buyable, $finalQty, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $orderItem->Quantity = $finalQty;

        try {
            $order->extend('afterSetQuantity', $orderItem, $buyable, $finalQty, $filter);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $orderItem->write();

        if (!$modOutcome->abort && $modOutcome->message && $modOutcome->messageType !== 'good') {
            $this->message($modOutcome->message, $modOutcome->messageType);
        } else {
            $this->message(_t(__CLASS__ . '.QuantitySet', 'Quantity has been set.'));
        }

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
        $item = $this->findLineItem($buyable, $filter);

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
        $item = $this->findLineItem($buyable, $customFilter);

        if (!$item instanceof OrderItem) {
            return $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        return $item;
    }

    /**
     * Find an existing cart line without updating {@see getMessage()} when the line does not exist.
     */
    public function findLineItem(Buyable $buyable, array $customFilter = []): ?OrderItem
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

        return $itemClass::get()->where($matchObjectFilter->getFilter())->first();
    }

    /**
     * Validate or adjust a cart modification via {@see Order::extend('updateCartItemModification', ...)}.
     */
    public function validateCartItemModification(CartItemModificationContext $context): CartItemModificationOutcome
    {
        $outcome = new CartItemModificationOutcome();
        $context->order->extend('updateCartItemModification', $context, $outcome);

        return $outcome;
    }

    /**
     * Switch a variation cart line to another variation of the same parent product (same validation path as quantity updates).
     */
    public function switchOrderItemVariation(OrderItem $orderItem, Variation $newVariation, array $filter = []): bool
    {
        $this->restoreCartContextFromLine($orderItem);

        $order = $this->current();

        if (!$order instanceof Order) {
            return (bool) $this->error(_t(__CLASS__ . '.NoOrder', 'No current order.'));
        }

        if ($orderItem->OrderID != $order->ID) {
            return (bool) $this->error(_t(__CLASS__ . '.ItemNotFound', 'Item not found.'));
        }

        if (!$orderItem instanceof VariationOrderItem) {
            return (bool) $this->error(
                _t(__CLASS__ . '.LineNotVariation', 'This cart line does not support switching variation.')
            );
        }

        if (!$newVariation->exists()) {
            return (bool) $this->error(_t(__CLASS__ . '.ProductNotFound', 'Product not found.'));
        }

        if ((int) $newVariation->ProductID !== (int) $orderItem->ProductID) {
            return (bool) $this->error(
                _t(__CLASS__ . '.VariationWrongProduct', 'That variation does not belong to this product.')
            );
        }

        $buyable = $this->getCorrectBuyable($newVariation);
        if (!$buyable instanceof Variation) {
            return (bool) $this->error(_t(__CLASS__ . '.ProductNotFound', 'Product not found.'));
        }

        if ((int) $orderItem->ProductVariationID === (int) $buyable->ID) {
            return true;
        }

        $lineQty = (int) $orderItem->Quantity;

        $duplicate = $this->findLineItem($buyable, []);
        if ($duplicate instanceof OrderItem && $duplicate->ID !== $orderItem->ID) {
            return (bool) $this->error(
                _t(__CLASS__ . '.VariationAlreadyInCart', 'That variation is already in your cart.')
            );
        }

        $modOutcome = $this->validateCartItemModification(
            new CartItemModificationContext(
                CartItemModificationContext::OP_SWITCH_VARIATION,
                $order,
                $orderItem,
                $buyable,
                $filter,
                0,
                $lineQty
            )
        );

        if ($modOutcome->abort) {
            if ($modOutcome->message !== null) {
                $this->message($modOutcome->message, $modOutcome->messageType);
            }

            return false;
        }

        $finalQty = $modOutcome->lineQuantityAfter ?? $lineQty;
        if ($finalQty < 0) {
            $finalQty = 0;
        }

        if ($finalQty === 0) {
            return (bool) $this->removeOrderItem($orderItem, null);
        }

        $member = Security::getCurrentUser();

        if (!$buyable->canPurchase($member, $finalQty)) {
            return (bool) $this->error(
                _t(
                    __CLASS__ . '.CannotPurchase',
                    'This {Title} cannot be purchased.',
                    '',
                    ['Title' => $buyable->i18n_singular_name()]
                )
            );
        }

        $orderItem->ProductVariationID = (int) $buyable->ID;
        $orderItem->Quantity = $finalQty;

        $orderItem->write();

        if (!$modOutcome->abort && $modOutcome->message && $modOutcome->messageType !== 'good') {
            $this->message($modOutcome->message, $modOutcome->messageType);
        } else {
            $this->message(_t(__CLASS__ . '.VariationUpdated', 'Variation has been updated.'));
        }

        return true;
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
        $order = Order::get()->filter(['Status:not' => 'Cart'])
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
     */
    protected function error(string $message): null
    {
        $this->message($message, 'bad');

        return null;
    }

    /**
     * Store a message to be fed back to user.
     *
     * @param DBField|string $message - the message to be stored (DB Field for link support - e.g. cart link)
     * @param string $type    - good, bad, warning
     */
    protected function message(DBField|string $message, string $type = 'good'): void
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function getMessage():  DBField|string
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

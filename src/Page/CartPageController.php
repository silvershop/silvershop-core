<?php

declare(strict_types=1);

namespace SilverShop\Page;

use PageController;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Extension\ViewableCartExtension;
use SilverShop\Forms\CartForm;
use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Model\OrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\Debug;
use SilverStripe\Model\ModelData;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionFailureException;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

/**
 * Cart page and HTTP entry point for cart changes. All user-facing cart mutations should flow through this controller
 * (or {@see ShoppingCart} when called from forms such as {@see CartForm}) so behaviour stays consistent.
 *
 * Actions are served under the cart page URL (see {@see CartPage::find_link()}), typically `/cart/...` when using the default setup.
 *
 * ## JSON responses (issue #33 baseline)
 *
 * Pass `?format=json` and/or `Accept: application/json`. Successful and error responses use JSON bodies with
 * `success`, `message`, and `messageType` mirroring {@see ShoppingCart::getMessage()}. HTML requests keep redirects /
 * `HTTPResponse_Exception` behaviour via {@see httpError()}.
 *
 * ## Validation / custom feedback (issue #90)
 *
 * All add, quantity, remove, and variation-switch paths consult `Order::extend('updateCartItemModification', $context, $outcome)`.
 * Extensions set `CartItemModificationOutcome` to block (`abort`), show messages, or clamp via `lineQuantityAfter` without throwing.
 *
 * @mixin ViewableCartExtension
 * @method ShoppingCart Cart()
 */
class CartPageController extends PageController
{
    private static string $url_segment = 'cart';

    private static string $disable_security_token = '';

    /**
     * Whether or not this controller redirects to the cart page whenever an item was added
     */
    private static bool $direct_to_cart_page = false;

    /**
     * @var ShoppingCart
     */
    protected $cart;

    private static array $allowed_actions = [
        'CartForm',
        'updatecart',
        'add',
        'additem',
        'addvariations',
        'remove',
        'removeitem',
        'removeall',
        'removeallitem',
        'setquantity',
        'setquantityitem',
        'clear',
        'debug',
        'switchvariation',
    ];

    /**
     * {@inheritDoc}
     *
     * Static config snapshots can omit newly added URL actions; ensure bulk variation POST is permitted.
     */
    public function allowedActions($limitToClass = null)
    {
        $actions = parent::allowedActions($limitToClass);

        if (!is_array($actions)) {
            $actions = [];
        }

        $present = [];
        foreach ($actions as $key => $value) {
            if (is_int($key)) {
                $present[strtolower((string) $value)] = true;
            } else {
                $present[strtolower((string) $key)] = true;
            }
        }

        foreach ($this->getCartUrlActionNames() as $name) {
            $lower = strtolower($name);
            if (!isset($present[$lower])) {
                $actions[] = $lower;
                $present[$lower] = true;
            }
        }

        return $actions;
    }

    /**
     * Actions routed from URL / Director (see {@see handleRequest()}).
     *
     * @return list<string>
     */
    private function getCartUrlActionNames(): array
    {
        return [
            'CartForm',
            'updatecart',
            'add',
            'additem',
            'addvariations',
            'remove',
            'removeitem',
            'removeall',
            'removeallitem',
            'setquantity',
            'setquantityitem',
            'clear',
            'debug',
            'switchvariation',
        ];
    }

    /**
     * The CMS Director rule puts the first URL segment after the page slug in the {@see HTTPRequest} `Action`
     * parameter (along with buyable class / ID). {@link RequestHandler} only inspects leftover path segments,
     * so cart mutations must be dispatched from that param.
     */
    public function handleRequest(HTTPRequest $request): HTTPResponse
    {
        $this->setRequest($request);
        $this->cart ??= ShoppingCart::singleton();

        $cartMethod = $this->directorActionToCartMethod($request->param('Action'));
        if ($cartMethod !== null) {
            $classMessage = Director::isLive() ? 'on this handler' : 'on class ' . static::class;

            try {
                if (!$this->hasAction($cartMethod)) {
                    return $this->httpError(404, "Action '{$cartMethod}' isn't available $classMessage.");
                }

                if (!$this->checkAccessAction($cartMethod)
                    || in_array(strtolower($cartMethod), ['run', 'doinit'], true)
                ) {
                    return $this->httpError(403, "Action '{$cartMethod}' isn't allowed $classMessage.");
                }

                $result = $this->handleAction($request, $cartMethod);
            } catch (HTTPResponse_Exception $e) {
                return $e->getResponse();
            } catch (PermissionFailureException $e) {
                return Security::permissionFailure(null, $e->getMessage());
            }

            if ($result instanceof HTTPResponse && $result->isError()) {
                return $result;
            }

            if ($this !== $result && $result instanceof \SilverStripe\Control\RequestHandler) {
                return $result->handleRequest($request);
            }

            if ($result instanceof HTTPResponse) {
                return $result;
            }

            if (is_array($result)) {
                return HTTPResponse::create($this->render($result));
            }

            return HTTPResponse::create((string) $result);
        }

        return parent::handleRequest($request);
    }

    /**
     * Map URL / Director `Action` parameter to a controller method name.
     */
    private function directorActionToCartMethod(mixed $actionParam): ?string
    {
        if ($actionParam === null || $actionParam === '') {
            return null;
        }

        $a = strtolower(str_replace('-', '_', (string) $actionParam));

        return match ($a) {
            'add', 'additem' => 'add',
            'remove', 'removeitem' => 'remove',
            'removeall', 'removeallitem' => 'removeall',
            'setquantity', 'setquantityitem' => 'setquantity',
            'addvariations', 'switchvariation', 'clear', 'debug', 'updatecart', 'cartform' => $a === 'cartform' ? 'CartForm' : $a,
            default => null,
        };
    }

    public static function add_item_link(Buyable $buyable, $parameters = []): bool|string
    {
        return self::build_url('add', $buyable, $parameters);
    }

    public static function remove_item_link(Buyable $buyable, $parameters = []): bool|string
    {
        return self::build_url('remove', $buyable, $parameters);
    }

    public static function remove_all_item_link(Buyable $buyable, $parameters = []): bool|string
    {
        return self::build_url('removeall', $buyable, $parameters);
    }

    public static function set_quantity_item_link(Buyable $buyable, $parameters = []): bool|string
    {
        return self::build_url('setquantity', $buyable, $parameters);
    }

    /**
     * Helper for creating a url
     */
    protected static function build_url($action, $buyable, $params = []): bool|string
    {
        if (!$action || !$buyable) {
            return false;
        }

        if (SecurityToken::is_enabled() && !static::config()->disable_security_token) {
            $params[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        $className = get_class($buyable);

        $link = Controller::join_links(
            CartPage::find_link(),
            $action,
            ShopTools::sanitiseClassName($className),
            $buyable->ID
        );

        return empty($params) ? $link : $link . '?' . http_build_query($params);
    }

    /**
     * Used by form submissions that need consistent redirect behaviour after cart changes.
     *
     * @param bool|string $status
     */
    public static function direct($status = true): string|HTTPResponse
    {
        if (Director::is_ajax()) {
            return (string)$status;
        }

        if (static::config()->direct_to_cart_page && ($cart = CartPage::find_link())) {
            return Controller::curr()->redirect($cart);
        }

        return Controller::curr()->redirectBack();
    }

    /**
     * Display a title if there is no model, or no title.
     */
    public function Title(): string
    {
        if ($this->getFailover && $this->getFailover()->Title) {
            return $this->getFailover()->Title;
        }

        return _t('SilverShop\Page\CartPage.DefaultTitle', 'Shopping Cart');
    }

    protected function init(): void
    {
        parent::init();
        $this->cart = ShoppingCart::singleton();
    }

    /**
     * A form for updating cart items
     */
    public function CartForm(): CartForm|bool
    {
        $cart = $this->Cart();
        if (!$cart) {
            return false;
        }

        $cartForm = CartForm::create($this, 'CartForm', $cart);

        $this->extend('updateCartForm', $cartForm);

        return $cartForm;
    }

    /**
     * Whether the client asked for a JSON payload (`?format=json` or `Accept: application/json`).
     */
    protected function wantsJson(HTTPRequest $request): bool
    {
        if ($request->getVar('format') === 'json') {
            return true;
        }

        $accept = (string) $request->getHeader('Accept', true);

        return str_contains($accept, 'application/json');
    }

    /**
     * Encode the cart feedback fields as JSON (also used by `cartFailure()` for machine clients).
     */
    protected function cartJsonResponse(bool $success, int $httpStatus = 200, array $extra = []): HTTPResponse
    {
        $payload = array_merge(
            [
                'success' => $success,
                'message' => (string) $this->cart->getMessage(),
                'messageType' => $this->cart->getMessageType(),
            ],
            $extra
        );
        $response = HTTPResponse::create(json_encode($payload), $httpStatus);
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');

        return $response;
    }

    /**
     * Non‑JSON callers raise {@see HTTPResponse_Exception} like {@see httpError()} so existing error handlers keep working.
     *
     * @return HTTPResponse|null JSON body for API clients, or null when an exception was thrown for HTML.
     */
    protected function cartFailure(HTTPRequest $request, int $status, ?string $message = null): ?HTTPResponse
    {
        $msg = $message ?? (string) $this->cart->getMessage();
        if ($msg === '' && $status === 404) {
            $msg = 'Not found';
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(false, $status, ['message' => $msg]);
        }

        $this->httpError($status, $msg);

        return null;
    }

    /**
     * @throws HTTPResponse_Exception
     */
    protected function buyableFromRequest(): Product|Variation|Buyable|null
    {
        $httpRequest = $this->getRequest();
        if (SecurityToken::is_enabled()
            && !static::config()->disable_security_token
            && !SecurityToken::inst()->checkRequest($httpRequest)
        ) {
            return $this->httpError(
                400,
                _t(
                    'SilverShop\Cart\ShoppingCart.InvalidSecurityToken',
                    'Invalid security token, possible CSRF attack.'
                )
            );
        }

        $id = 0;
        $buyableclass = Product::class;
        $buy = $httpRequest->param('Buyable');
        $idSeg = $httpRequest->param('ID');
        $otherSeg = $httpRequest->param('OtherID');

        if ($buy !== null && $buy !== '') {
            $buyableclass = ShopTools::unsanitiseClassName((string) $buy);
            $id = (int) $idSeg;
        } elseif ($idSeg !== null && $idSeg !== '' && !ctype_digit((string) $idSeg)) {
            $buyableclass = ShopTools::unsanitiseClassName((string) $idSeg);
            $id = (int) $otherSeg;
        } elseif ($otherSeg !== null && $otherSeg !== '' && !ctype_digit((string) $otherSeg)) {
            // Some stacks may assign sanitised class to OtherID and numeric PK to ID
            $buyableclass = ShopTools::unsanitiseClassName((string) $otherSeg);
            $id = (int) $idSeg;
        } else {
            $id = (int) ($idSeg ?: $otherSeg);
        }

        if ($id === 0) {
            return null;
        }

        if (!ClassInfo::exists($buyableclass)) {
            return null;
        }

        $buyable = $buyableclass::has_extension(Versioned::class)
            ? Versioned::get_by_stage($buyableclass, 'Live')->byID($id)
            : DataObject::get($buyableclass)->byID($id);

        if (!$buyable || !($buyable instanceof Buyable)) {
            return null;
        }

        return $this->cart->getCorrectBuyable($buyable);
    }

    /**
     * Action: add item to cart
     *
     * @throws HTTPResponse_Exception
     */
    public function add($request): string|HTTPResponse|null
    {
        $response = null;
        $result = false;
        $product = null;
        $rawQty = $request->getVar('quantity');
        $quantity = $rawQty === null ? 1 : max(0, (int) $rawQty);

        if (($product = $this->buyableFromRequest()) instanceof Buyable) {
            if ($quantity === 0) {
                $result = $this->cart->remove($product, null, $request->getVars());
            } else {
                $result = $this->cart->add($product, $quantity, $request->getVars());
            }

            if (!$result) {
                return $this->cartFailure($request, 400);
            }
        } else {
            return $this->cartFailure($request, 404);
        }

        $this->updateLocale($request);
        $this->extend('updateAddResponse', $request, $response, $product, $quantity, $result);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            $item = $result instanceof OrderItem ? $result : null;

            return $this->cartJsonResponse(
                true,
                200,
                $item ? ['itemId' => $item->ID] : []
            );
        }

        return self::direct($result);
    }

    /**
     * Add multiple variations for one product in a single POST (bulk form from product page).
     *
     * Expects: ProductID, VariantQuantity[variationID] => int (quantities &lt;= 0 are skipped).
     *
     * @throws HTTPResponse_Exception
     */
    public function addvariations(HTTPRequest $request): string|HTTPResponse|null
    {
        $response = null;

        if (!$request->isPOST()) {
            return $this->httpError(405);
        }

        if (SecurityToken::is_enabled() && !SecurityToken::inst()->checkRequest($request)) {
            return $this->httpError(
                400,
                _t(
                    'SilverShop\Cart\ShoppingCart.InvalidSecurityToken',
                    'Invalid security token, possible CSRF attack.'
                )
            );
        }

        $productId = (int) $request->postVar('ProductID');
        if ($productId === 0) {
            return $this->httpError(400);
        }

        $productClass = Product::class;
        $product = $productClass::has_extension(Versioned::class)
            ? Versioned::get_by_stage($productClass, 'Live')->byID($productId)
            : DataObject::get($productClass)->byID($productId);

        if (!$product instanceof Product) {
            return $this->httpError(404);
        }

        $rawQuantities = $request->postVar('VariantQuantity');
        $quantities = is_array($rawQuantities) ? $rawQuantities : [];

        /** @var list<array{0: Buyable, 1: int}> $toAdd */
        $toAdd = [];

        foreach ($quantities as $variationId => $qty) {
            $variationId = (int) $variationId;
            $qty = max(0, (int) $qty);

            if ($variationId === 0 || $qty === 0) {
                continue;
            }

            $variationClass = Variation::class;
            $variation = $variationClass::has_extension(Versioned::class)
                ? Versioned::get_by_stage($variationClass, 'Live')->byID($variationId)
                : DataObject::get($variationClass)->byID($variationId);

            if (!$variation instanceof Variation || (int) $variation->ProductID !== (int) $product->ID) {
                return $this->httpError(400);
            }

            $buyable = $this->cart->getCorrectBuyable($variation);

            if (!$buyable instanceof Buyable) {
                return $this->httpError(400, $this->cart->getMessage());
            }

            $toAdd[] = [$buyable, $qty];
        }

        foreach ($toAdd as [$buyable, $qty]) {
            $addResult = $this->cart->add($buyable, $qty, $request->getVars());

            if (!$addResult) {
                return $this->cartFailure($request, 400);
            }
        }

        $this->updateLocale($request);
        $this->extend('updateAddVariationsResponse', $request, $response, $product, $quantities);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(true);
        }

        return self::direct(true);
    }

    /**
     * Switch an existing cart line to another {@see Variation} of the same product.
     *
     * Query / POST parameters: `ItemID` (order item), `VariationID` (target live variation).
     *
     * @throws HTTPResponse_Exception When the browser expects HTML and the operation failed.
     */
    public function switchvariation(HTTPRequest $request): string|HTTPResponse|null
    {
        $response = null;

        if (SecurityToken::is_enabled()
            && !static::config()->disable_security_token
            && !SecurityToken::inst()->checkRequest($request)
        ) {
            return $this->cartFailure(
                $request,
                400,
                _t(
                    'SilverShop\Cart\ShoppingCart.InvalidSecurityToken',
                    'Invalid security token, possible CSRF attack.'
                )
            );
        }

        $itemId = (int) $request->requestVar('ItemID');
        $variationId = (int) $request->requestVar('VariationID');
        if ($itemId === 0 || $variationId === 0) {
            return $this->cartFailure($request, 400, 'Missing ItemID or VariationID.');
        }

        $item = OrderItem::get()->byID($itemId);
        if (!$item instanceof OrderItem) {
            return $this->cartFailure($request, 404);
        }

        $order = $item->Order();
        if (!$order->exists() || !$order->IsCart()) {
            return $this->cartFailure($request, 400);
        }

        $this->cart->setCurrent($order);

        $variation = Variation::has_extension(Versioned::class)
            ? Versioned::get_by_stage(Variation::class, 'Live')->byID($variationId)
            : DataObject::get(Variation::class)->byID($variationId);

        if (!$variation instanceof Variation) {
            return $this->cartFailure($request, 404);
        }

        if (!$this->cart->switchOrderItemVariation($item, $variation)) {
            return $this->cartFailure($request, 400);
        }

        $this->updateLocale($request);
        $this->extend('updateSwitchVariationResponse', $request, $response, $item, $variation);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(true, 200, ['itemId' => $item->ID, 'variationId' => $variation->ID]);
        }

        return self::direct(true);
    }

    /**
     * Action: remove a certain number of items from the cart
     *
     * @throws HTTPResponse_Exception
     */
    public function remove($request): string|HTTPResponse
    {
        $response = null;
        $product = null;
        $quantity = 1;

        if (($product = $this->buyableFromRequest()) instanceof Buyable) {
            $cartResult = $this->cart->remove($product, $quantity, $request->getVars());
            if ($cartResult === null) {
                return $this->cartFailure($request, 400);
            }

            if ($cartResult === false) {
                return $this->cartFailure($request, 404);
            }
        } else {
            return $this->cartFailure($request, 404);
        }

        $this->updateLocale($request);
        $this->extend('updateRemoveResponse', $request, $response, $product, $quantity);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(true);
        }

        return self::direct();
    }

    /**
     * Action: remove all of an item from the cart
     *
     * @throws HTTPResponse_Exception
     */
    public function removeall($request): string|HTTPResponse
    {
        $response = null;

        if (($product = $this->buyableFromRequest()) instanceof Buyable) {
            $cartResult = $this->cart->remove($product, null, $request->getVars());
            if ($cartResult === null) {
                return $this->cartFailure($request, 400);
            }

            if ($cartResult === false) {
                return $this->cartFailure($request, 404);
            }
        } else {
            return $this->cartFailure($request, 404);
        }

        $this->updateLocale($request);
        $this->extend('updateRemoveAllResponse', $request, $response, $product);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(true);
        }

        return self::direct();
    }

    /**
     * Action: update the quantity of an item in the cart
     *
     * @throws HTTPResponse_Exception
     */
    public function setquantity($request): string|HTTPResponse
    {
        $response = null;
        $product = $this->buyableFromRequest();
        $quantity = max(0, (int) $request->getVar('quantity'));

        if ($product instanceof Buyable) {
            $result = $this->cart->setQuantity($product, $quantity, $request->getVars());
            if ($result === false || $result === null) {
                return $this->cartFailure($request, 400);
            }
        } else {
            return $this->cartFailure($request, 404);
        }

        $this->updateLocale($request);
        $this->extend('updateSetQuantityResponse', $request, $response, $product, $quantity);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(true);
        }

        return self::direct();
    }

    /**
     * Action: clear the cart
     */
    public function clear($request): string|HTTPResponse
    {
        $response = null;
        $this->updateLocale($request);
        $cleared = $this->cart->clear();
        if ($cleared === null) {
            return $this->cartFailure($request, 400);
        }

        $this->extend('updateClearResponse', $request, $response);

        if ($response instanceof HTTPResponse) {
            return $response;
        }

        if ($this->wantsJson($request)) {
            return $this->cartJsonResponse(true);
        }

        return self::direct();
    }

    /**
     * Displays order info and cart contents.
     */
    public function debug(): ModelData|string
    {
        if (Director::isDev() || Permission::check('ADMIN')) {
            Requirements::css('silvershop/core:client/dist/css/cartdebug.css');
            $order = ShoppingCart::curr();
            $content = ($order instanceof Order)
                ? Debug::text($order)
                : 'Cart has not been created yet. Add a product.';

            return ['Content' => $content];
        }
    }

    /**
     * @param HTTPRequest $request
     */
    protected function updateLocale($request)
    {
        $order = $this->cart->current();
        if ($request && $request->isAjax() && $order) {
            ShopTools::install_locale($order->Locale);
        }
    }
}

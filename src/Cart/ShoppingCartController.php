<?php

declare(strict_types=1);

namespace SilverShop\Cart;

use SilverStripe\Model\ModelData;
use SilverShop\Extension\ViewableCartExtension;
use SilverShop\Model\Buyable;
use SilverShop\Model\Order;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\CartPage;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\Debug;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

/**
 * Manipulate the cart via urls.
 *
 * @mixin  ViewableCartExtension
 * @method ShoppingCart Cart()
 */
class ShoppingCartController extends Controller
{
    private static string $url_segment = 'shoppingcart';

    private static string $disable_security_token = '';

    /**
     * Whether or not this controller redirects to the cart-page whenever an item was added
     */
    private static bool $direct_to_cart_page = false;

    /**
     * @var ShoppingCart
     */
    protected $cart;

    private static array $url_handlers = [
        // Require three segments for buyable actions so a single segment (e.g. addvariations, clear) is not
        // captured as $Action with optional empty $Buyable/$ID (which breaks request parsing).
        '$Action!/$Buyable!/$ID!' => 'handleAction',
        '$Action' => '$Action',
    ];

    private static array $allowed_actions = [
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
            return $actions;
        }

        foreach (array_values($actions) as $value) {
            if (is_string($value) && strtolower($value) === 'addvariations') {
                return $actions;
            }
        }

        $actions[] = 'addvariations';

        return $actions;
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

        if (SecurityToken::is_enabled() && !self::config()->disable_security_token) {
            $params[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        $className = get_class($buyable);

        $link = Controller::join_links(
            [
            self::config()->url_segment,
            $action,
            ShopTools::sanitiseClassName($className),
            $buyable->ID
            ]
        );

        return empty($params) ? $link : $link . '?' . http_build_query($params);
    }

    /**
     * This is used here and in VariationForm and AddProductForm
     *
     * @param bool|string $status
     */
    public static function direct($status = true): string|HTTPResponse
    {
        if (Director::is_ajax()) {
            return (string)$status;
        }

        if (self::config()->direct_to_cart_page && ($cart = CartPage::find_link())) {
            return Controller::curr()->redirect($cart);
        }

        return Controller::curr()->redirectBack();
    }

    protected function init(): void
    {
        parent::init();
        $this->cart = ShoppingCart::singleton();
    }

    /**
     * @throws HTTPResponse_Exception
     */
    protected function buyableFromRequest(): Product|Variation|Buyable|null
    {
        $httpRequest = $this->getRequest();
        if (SecurityToken::is_enabled()
            && !self::config()->disable_security_token
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

        $id = (int)$httpRequest->param('ID');
        if ($id === 0) {
            //TODO: store error message
            return null;
        }

        $buyableclass = Product::class;
        if ($class = $httpRequest->param('Buyable')) {
            $buyableclass = ShopTools::unsanitiseClassName($class);
        }

        if (!ClassInfo::exists($buyableclass)) {
            //TODO: store error message
            return null;
        }

        //ensure only live products are returned, if they are versioned
        $buyable = $buyableclass::has_extension(Versioned::class)
            ? Versioned::get_by_stage($buyableclass, 'Live')->byID($id)
            : DataObject::get($buyableclass)->byID($id);

        if (!$buyable || !($buyable instanceof Buyable)) {
            //TODO: store error message
            return null;
        }

        return $this->cart->getCorrectBuyable($buyable);
    }

    /**
     * Action: add item to cart
     *
     * @param HTTPRequest $request
     * @throws HTTPResponse_Exception
     */
    public function add($request): string|HTTPResponse|null
    {
        $result = false;

        if (($product = $this->buyableFromRequest()) instanceof Buyable) {
            $quantityParam = $request->getVar('quantity');
            $quantity = ($quantityParam !== null) ? max(0, (int)$quantityParam) : 1;

            if ($quantity === 0) {
                $result = $this->cart->remove($product, null, $request->getVars());
            } else {
                $result = $this->cart->add($product, $quantity, $request->getVars());
            }

            if (!$result) {
                $response = $this->httpError(400, $this->cart->getMessage());
            }
        } else {
            $response = $this->httpError(404);
        }

        $this->updateLocale($request);
        $this->extend('updateAddResponse', $request, $response, $product, $quantity, $result);

        return $response ? $response : self::direct($result);
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

        // Always validate CSRF for this POST, even when disable_security_token relaxes GET cart URLs.
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
                $response = $this->httpError(400, $this->cart->getMessage());
                break;
            }
        }

        $this->updateLocale($request);
        $this->extend('updateAddVariationsResponse', $request, $response, $product, $quantities);

        return $response ? $response : self::direct(true);
    }

    /**
     * Action: remove a certain number of items from the cart
     *
     * @param HTTPRequest $request
     * @throws HTTPResponse_Exception
     */
    public function remove($request): string|HTTPResponse
    {
        if (($product = $this->buyableFromRequest()) instanceof Buyable) {
            $this->cart->remove($product, $quantity = 1, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateRemoveResponse', $request, $response, $product, $quantity);
        return $response ? $response : self::direct();
    }

    /**
     * Action: remove all of an item from the cart
     *
     * @param HTTPRequest $request
     * @throws HTTPResponse_Exception
     */
    public function removeall($request): string|HTTPResponse
    {
        if (($product = $this->buyableFromRequest()) instanceof Buyable) {
            $this->cart->remove($product, null, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateRemoveAllResponse', $request, $response, $product);
        return $response ? $response : self::direct();
    }

    /**
     * Action: update the quantity of an item in the cart
     *
     * @param HTTPRequest $request
     * @throws HTTPResponse_Exception
     */
    public function setquantity($request): string|HTTPResponse
    {
        $product = $this->buyableFromRequest();
        $quantity = max(0, (int)$request->getVar('quantity'));
        if ($product instanceof Buyable) {
            $this->cart->setQuantity($product, $quantity, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateSetQuantityResponse', $request, $response, $product, $quantity);
        return $response ? $response : self::direct();
    }

    /**
     * Action: clear the cart
     *
     * @param HTTPRequest $request
     */
    public function clear($request): string|HTTPResponse
    {
        $this->updateLocale($request);
        $this->cart->clear();
        $this->extend('updateClearResponse', $request, $response);
        return $response ? $response : self::direct();
    }

    /**
     * Handle index requests
     *
     * @throws HTTPResponse_Exception
     */
    public function index()
    {
        if ($this->Cart() && CartPage::find_link()) {
            return $this->redirect(CartPage::find_link());
        }

        if ($response = ErrorPage::response_for(404)) {
            return $response;
        }

        return $this->httpError(404, _t('SilverShop\Cart\ShoppingCart.NoCartInitialised', 'no cart initialised'));
    }

    /**
     * Displays order info and cart contents.
     */
    public function debug(): ModelData|string
    {
        if (Director::isDev() || Permission::check('ADMIN')) {
            //TODO: allow specifying a particular id to debug
            Requirements::css('silvershop/core: client/dist/css/cartdebug.css');
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

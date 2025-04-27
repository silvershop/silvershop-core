<?php

namespace SilverShop\Cart;

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
        '$Action/$Buyable/$ID' => 'handleAction',
    ];

    private static array $allowed_actions = [
        'add',
        'additem',
        'remove',
        'removeitem',
        'removeall',
        'removeallitem',
        'setquantity',
        'setquantityitem',
        'clear',
        'debug',
    ];

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
     *
     * @return string|HTTPResponse
     */
    public static function direct($status = true)
    {
        if (Director::is_ajax()) {
            return (string)$status;
        }

        if (self::config()->direct_to_cart_page && ($cart = CartPage::find_link())) {
            return Controller::curr()->redirect($cart);
        }
        return Controller::curr()->redirectBack();
    }

    public function init(): void
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
            $quantity = (int)$request->getVar('quantity');

            if ($quantity === 0) {
                $quantity = 1;
            }

            $result = $this->cart->add($product, $quantity, $request->getVars());

            if ($result) {
                $response = $this->cart->getMessage();
            } else {
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
        $quantity = (int)$request->getVar('quantity');
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
    public function debug()
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

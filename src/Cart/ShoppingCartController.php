<?php

namespace SilverShop\Core\Cart;
use SilverStripe\Control\Controller;


/**
 * Manipulate the cart via urls.
 */
class ShoppingCartController extends Controller
{
    private static $url_segment         = "shoppingcart";

    private static $direct_to_cart_page = false;

    protected      $cart;

    private static $url_handlers        = array(
        '$Action/$Buyable/$ID' => 'handleAction',
    );

    private static $allowed_actions     = array(
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
    );

    public static function add_item_link(Buyable $buyable, $parameters = array())
    {
        return self::build_url("add", $buyable, $parameters);
    }

    public static function remove_item_link(Buyable $buyable, $parameters = array())
    {
        return self::build_url("remove", $buyable, $parameters);
    }

    public static function remove_all_item_link(Buyable $buyable, $parameters = array())
    {
        return self::build_url("removeall", $buyable, $parameters);
    }

    public static function set_quantity_item_link(Buyable $buyable, $parameters = array())
    {
        return self::build_url("setquantity", $buyable, $parameters);
    }

    /**
     * Helper for creating a url
     */
    protected static function build_url($action, $buyable, $params = array())
    {
        if (!$action || !$buyable) {
            return false;
        }
        if (SecurityToken::is_enabled() && !self::config()->disable_security_token) {
            $params[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }
        return self::config()->url_segment . '/' .
            $action . '/' .
            $buyable->class . "/" .
            $buyable->ID .
            self::params_to_get_string($params);
    }

    /**
     * Creates the appropriate string parameters for links from array
     *
     * Produces string such as: MyParam%3D11%26OtherParam%3D1
     *     ...which decodes to: MyParam=11&OtherParam=1
     *
     * you will need to decode the url with javascript before using it.
     */
    protected static function params_to_get_string($array)
    {
        if ($array & count($array > 0)) {
            array_walk($array, create_function('&$v,$k', '$v = $k."=".$v ;'));
            return "?" . implode("&", $array);
        }
        return "";
    }

    /**
     * This is used here and in VariationForm and AddProductForm
     *
     * @param bool|string $status
     *
     * @return bool
     */
    public static function direct($status = true)
    {
        if (Director::is_ajax()) {
            return $status;
        }
        if (self::config()->direct_to_cart_page && $cartlink = CartPage::find_link()) {
            Controller::curr()->redirect($cartlink);
            return;
        } else {
            Controller::curr()->redirectBack();
            return;
        }
    }

    public function init()
    {
        parent::init();
        $this->cart = ShoppingCart::singleton();
    }

    /**
     * @return Product|ProductVariation|Buyable
     */
    protected function buyableFromRequest()
    {
        $request = $this->getRequest();
        if (
            SecurityToken::is_enabled() &&
            !self::config()->disable_security_token &&
            !SecurityToken::inst()->checkRequest($request)
        ) {
            return $this->httpError(
                400,
                _t("ShoppingCart.InvalidSecurityToken", "Invalid security token, possible CSRF attack.")
            );
        }
        $id = (int)$request->param('ID');
        if (empty($id)) {
            //TODO: store error message
            return null;
        }
        $buyableclass = "Product";
        if ($class = $request->param('Buyable')) {
            $buyableclass = Convert::raw2sql($class);
        }
        if (!ClassInfo::exists($buyableclass)) {
            //TODO: store error message
            return null;
        }
        //ensure only live products are returned, if they are versioned
        $buyable = Object::has_extension($buyableclass, Versioned::class)
            ?
            Versioned::get_by_stage($buyableclass, 'Live')->byID($id)
            :
            DataObject::get($buyableclass)->byID($id);
        if (!$buyable || !($buyable instanceof Buyable)) {
            //TODO: store error message
            return null;
        }

        return $this->cart->getCorrectBuyable($buyable);
    }

    /**
     * Action: add item to cart
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse
     */
    public function add($request)
    {
        if ($product = $this->buyableFromRequest()) {
            $quantity = (int)$request->getVar('quantity');
            if (!$quantity) {
                $quantity = 1;
            }
            $this->cart->add($product, $quantity, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateAddResponse', $request, $response, $product, $quantity);
        return $response ? $response : self::direct();
    }

    /**
     * Action: remove a certain number of items from the cart
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse
     */
    public function remove($request)
    {
        if ($product = $this->buyableFromRequest()) {
            $this->cart->remove($product, $quantity = 1, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateRemoveResponse', $request, $response, $product, $quantity);
        return $response ? $response : self::direct();
    }

    /**
     * Action: remove all of an item from the cart
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse
     */
    public function removeall($request)
    {
        if ($product = $this->buyableFromRequest()) {
            $this->cart->remove($product, null, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateRemoveAllResponse', $request, $response, $product);
        return $response ? $response : self::direct();
    }

    /**
     * Action: update the quantity of an item in the cart
     *
     * @param SS_HTTPRequest $request
     *
     * @return AjaxHTTPResponse|bool
     */
    public function setquantity($request)
    {
        $product = $this->buyableFromRequest();
        $quantity = (int)$request->getVar('quantity');
        if ($product) {
            $this->cart->setQuantity($product, $quantity, $request->getVars());
        }

        $this->updateLocale($request);
        $this->extend('updateSetQuantityResponse', $request, $response, $product, $quantity);
        return $response ? $response : self::direct();
    }

    /**
     * Action: clear the cart
     *
     * @param SS_HTTPRequest $request
     *
     * @return AjaxHTTPResponse|bool
     */
    public function clear($request)
    {
        $this->updateLocale($request);
        $this->cart->clear();
        $this->extend('updateClearResponse', $request, $response);
        return $response ? $response : self::direct();
    }

    /**
     * Handle index requests
     */
    public function index()
    {
        if ($cart = $this->Cart()) {
            $this->redirect($cart->CartLink);
            return;
        } elseif ($response = ErrorPage::response_for(404)) {
            return $response;
        }
        return $this->httpError(404, _t("ShoppingCart.NoCartInitialised", "no cart initialised"));
    }

    /**
     * Displays order info and cart contents.
     */
    public function debug()
    {
        if (Director::isDev() || Permission::check("ADMIN")) {
            //TODO: allow specifying a particular id to debug
            Requirements::css(SHOP_DIR . "/css/cartdebug.css");
            $order = ShoppingCart::curr();
            $content = ($order)
                ?
                Debug::text($order)
                :
                "Cart has not been created yet. Add a product.";
            return array('Content' => $content);
        }
    }

    protected function updateLocale($request)
    {
        $order = $this->cart->current();
        if ($request && $request->isAjax() && $order) {
            ShopTools::install_locale($order->Locale);
        }
    }
}

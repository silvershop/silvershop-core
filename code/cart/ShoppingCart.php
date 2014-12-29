<?php

/**
 * Encapsulated manipulation of the current order using a singleton pattern.
 *
 * Ensures that an order is only started (persisted to DB) when necessary,
 * and all future changes are on the same order, until the order has is placed.
 * The requirement for starting an order is to adding an item to the cart.
 *
 * @package shop
 */
class ShoppingCart {

	private static $cartid_session_name = 'shoppingcartid';

	private $order;

	private $calculateonce = false;

	private $message;

	private $type;

	private static $instance;

	/**
	 * Access for only allowing access to one (singleton) ShoppingCart.
	 *
	 * @return ShoppingCart
	 */
	public static function singleton() {
		if(self::$instance === null) {
			self::$instance = new ShoppingCart();
		}

		return self::$instance;
	}

	/**
	 * Shortened alias for ShoppingCart::singleton()->current()
	 *
	 * @return Order
	 */
	public static function curr() {
		return self::singleton()->current();
	}

	/**
	 * Singleton prevents constructing a ShoppingCart any other way.
	 */
	private function __construct() {

	}

	/**
	 * Get the current order, or return null if it doesn't exist.
	 *
	 * @return Order
	 */
	public function current() {
		//find order by id saved to session (allows logging out and retaining cart contents)
		if (!$this->order && $sessionid = Session::get(self::$cartid_session_name)) {
			$this->order = Order::get()->filter(array(
				"Status" => "Cart",
				"ID" => $sessionid
			))->first();
		}
		if(!$this->calculateonce && $this->order) {
			$this->order->calculate();
			$this->calculateonce = true;
		}

		return $this->order ? $this->order : false;
	}

	/**
	 * Set the current cart
	 *
	 * @param Order
	 *
	 * @return ShoppingCart
	 */
	public function setCurrent(Order $cart) {
		if(!$cart->IsCart()) {
			trigger_error("Passed Order object is not cart status", E_ERROR);
		}
		$this->order = $cart;
		Session::set(self::$cartid_session_name, $cart->ID);

		return $this;
	}

	/**
	 * Helper that only allows orders to be started internally.
	 *
	 * @return Order
	 */
	protected function findOrMake() {
		if($this->current()){
			return $this->current();
		}
		$this->order = Order::create();
		if(Member::config()->login_joins_cart && Member::currentUserID()) {
			$this->order->MemberID = Member::currentUserID();
		}
		$this->order->write();
		$this->order->extend('onStartOrder');
		Session::set(self::$cartid_session_name, $this->order->ID);

		return $this->order;
	}

	/**
	 * Adds an item to the cart
	 *
	 * @param Buyable $buyable
	 * @param number $quantity
	 * @param unknown $filter
	 * @return boolean|OrderItem false or the new/existing item
	 */
	public function add(Buyable $buyable, $quantity = 1, $filter = array()) {
		$order = $this->findOrMake();
		$order->extend("beforeAdd", $buyable, $quantity, $filter);
		if(!$buyable) {

			return $this->error(_t("ShoppingCart.PRODUCTNOTFOUND", "Product not found."));
		}
		$item = $this->findOrMakeItem($buyable, $filter);
		if(!$item) {
			
			return false;
		}
		if(!$item->_brandnew) {
			$item->Quantity += $quantity;
		} else {
			$item->Quantity = $quantity;
		}
		$item->write();
		$order->extend("afterAdd", $item, $buyable, $quantity, $filter);
		$this->message(_t("ShoppingCart.ITEMADD", "Item has been added successfully."));

		return $item;
	}

	/**
	 * Remove an item from the cart.
	 *
	 * @param id or Buyable $buyable
	 * @param $item
	 * @param int $quantity - number of items to remove, or leave null for all items (default)
	 * @return boolean success/failure
	 */
	public function remove(Buyable $buyable, $quantity = null, $filter = array()) {
		$order = $this->current();

		if(!$order) {
			return $this->error(_t("ShoppingCart.NOORDER", "No current order."));
		}

		$order->extend("beforeRemove", $buyable, $quantity, $filter);

		$item = $this->get($buyable, $filter);
		
		if(!$item) {
			return false;
		}

		//if $quantity will become 0, then remove all
		if(!$quantity || ($item->Quantity - $quantity) <= 0){
			$item->delete();
			$item->destroy();
		}else{
			$item->Quantity -= $quantity;
			$item->write();
		}
		$order->extend("afterRemove", $item, $buyable, $quantity, $filter);
		$this->message(_t("ShoppingCart.ITEMREMOVED", "Item has been successfully removed."));
		
		return true;
	}

	/**
	 * Sets the quantity of an item in the cart.
	 * Will automatically add or remove item, if necessary.
	 *
	 * @param id or Buyable $buyable
	 * @param $item
	 * @param int $quantity
	 * @return boolean|OrderItem false or the new/existing item
	 */
	public function setQuantity(Buyable $buyable, $quantity = 1, $filter = array()) {
		if($quantity <= 0) {
			return $this->remove($buyable, $quantity, $filter);
		}
		$order = $this->findOrMake();
		$item = $this->findOrMakeItem($buyable, $filter);
		if(!$item) {

			return false;
		}
		$order->extend("beforeSetQuantity", $buyable, $quantity, $filter);
		$item->Quantity = $quantity;
		$item->write();
		$order->extend("afterSetQuantity", $item, $buyable, $quantity, $filter);
		$this->message(_t("ShoppingCart.QUANTITYSET", "Quantity has been set."));
		
		return $item;
	}

	/**
	 * Finds or makes an order item for a given product + filter.
	 * @param id or Buyable $buyable
	 * @param string $filter
	 * @return OrderItem the found or created item
	 */
	private function findOrMakeItem(Buyable $buyable,$filter = array()) {
		$order = $this->findOrMake();
	
		if(!$buyable || !$order){
			return false;
		}
	
		$item = $this->get($buyable, $filter);
	
		if(!$item) {
			$member = Member::currentUser();

			if(!$buyable->canPurchase($member)) {
				return $this->error(
					sprintf(_t("ShoppingCart.CANNOTPURCHASE",
						"This %s cannot be purchased."),
						strtolower($buyable->i18n_singular_name())
					)
				);
				//TODO: produce a more specific message
			}

			$item = $buyable->createItem(1, $filter);
			$item->OrderID = $order->ID;
			$item->write();

			$order->Items()->add($item);

			$item->_brandnew = true; // flag as being new
		}

		return $item;
	}

	/**
	 * Finds an existing order item.
	 * @param Buyable $buyable
	 * @param string $filter
	 * @return the item requested, or false
	 */
	public function get(Buyable $buyable, $customfilter = array()) {
		$order = $this->current();
		if(!$buyable || !$order){
			return false;
		}
		$filter = array(
			'OrderID' => $order->ID
		);
		$itemclass = Config::inst()->get(get_class($buyable), 'order_item');
		$relationship = Config::inst()->get($itemclass, 'buyable_relationship');
		$filter[$relationship."ID"] = $buyable->ID;
		$required = array('Order',$relationship);
		if(is_array($itemclass::config()->required_fields)){
			$required = array_merge($required, $itemclass::config()->required_fields);
		}
		$query = new MatchObjectFilter($itemclass, array_merge($customfilter, $filter), $required);
		$item = $itemclass::get()->where($query->getFilter())->first();
		if(!$item){
			return $this->error(_t("ShoppingCart.ITEMNOTFOUND", "Item not found."));
		}

		return $item;
	}

	/**
	 * Store old cart id in session order history
	 */
	public function archiveorderid() {
		$order = Order::get()
			->filter("Status:not", "Cart")
			->byId(Session::get(self::$cartid_session_name));
		if($order && !$order->IsCart()){
			OrderManipulation::add_session_order($order);
		}
		$this->clear();
	}

	/**
	 * Empty / abandon the entire cart.
	 * @return bool - true if successful, false if no cart found
	 */
	public function clear() {
		Session::clear(self::$cartid_session_name);
		$order = $this->current();
		$this->order = null;
		if(!$order){
			return $this->error(_t("ShoppingCart.NOCARTFOUND", "No cart found."));
		}
		$order->write();	
		$this->message(_t("ShoppingCart.CLEARED", "Cart was successfully cleared."));

		return true;
	}

	/**
	 * Store a new error.
	 */
	protected function error($message) {
		$this->message($message, "bad");
		
		return false;
	}

	/**
	 * Store a message to be fed back to user.
	 * @param string $message
	 * @param string $type - good, bad, warning
	 */
	protected function message($message, $type = "good") {
		$this->message = $message;
		$this->type = $type;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getMessageType() {
		return $this->type;
	}

	//singleton protection
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}

}

/**
 * Manipulate the cart via urls.
 */
class ShoppingCart_Controller extends Controller{

	private static $url_segment = "shoppingcart";
	private static $direct_to_cart_page = false;
	protected $cart;

	private static $url_handlers = array(
		'$Action/$Buyable/$ID' => 'handleAction',
	);

	private static $allowed_actions = array(
		'add',
		'additem',
		'remove',
		'removeitem',
		'removeall',
		'removeallitem',
		'setquantity',
		'setquantityitem',
		'clear',
		'debug'
	);

	public static function add_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("add", $buyable, $parameters);
	}
	public static function remove_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("remove", $buyable, $parameters);
	}
	public static function remove_all_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("removeall", $buyable, $parameters);
	}
	public static function set_quantity_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("setquantity", $buyable, $parameters);
	}

	/**
	 * Helper for creating a url
	 */
	protected static function build_url($action, $buyable, $params = array()) {
		if(!$action || !$buyable){
			return false;
		}
		if(SecurityToken::is_enabled()){
			$params[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
		}
		return self::config()->url_segment.'/'.
				$action.'/'.
				$buyable->class."/".
				$buyable->ID.
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
	protected static function params_to_get_string($array) {
		if($array & count($array > 0)){
			array_walk($array, create_function('&$v,$k', '$v = $k."=".$v ;'));
			return "?".implode("&", $array);
		}
		return "";
	}


	/**
	 * This is used here and in VariationForm and AddProductForm
	 * @param bool|string $status
	 * @return bool
	 */
	public static function direct($status = true) {
		if(Director::is_ajax()){
			return $status;
		}
		if(self::config()->direct_to_cart_page && $cartlink = CartPage::find_link()){
			Controller::curr()->redirect($cartlink);
			return;
		}else{
			Controller::curr()->redirectBack();
			return;
		}
	}

	public function init() {
		parent::init();
		$this->cart = ShoppingCart::singleton();
	}


	/**
	 * @return Product|ProductVariation|Buyable
	 */
	protected function buyableFromRequest() {
		$request = $this->getRequest();
		if(SecurityToken::is_enabled() && !SecurityToken::inst()->checkRequest($request)){
			return $this->httpError(400,
				_t("ShoppingCart.CSRF", "Invalid security token, possible CSRF attack.")
			);
		}
		$id = (int) $request->param('ID');
		if(empty($id)){
			//TODO: store error message
			return null;
		}
		$buyableclass = "Product";
		if($class = $request->param('Buyable')){
			$buyableclass = Convert::raw2sql($class);
		}
		if(!ClassInfo::exists($buyableclass)){
			//TODO: store error message
			return null;
		}
		//ensure only live products are returned, if they are versioned
		$buyable = Object::has_extension($buyableclass, 'Versioned') ?
			Versioned::get_by_stage($buyableclass, 'Live')->byID($id) :
			DataObject::get($buyableclass)->byID($id);
		if(!$buyable || !($buyable instanceof Buyable)){
			//TODO: store error message
			return null;
		}
		return $buyable;
	}


	/**
	 * Action: add item to cart
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function add($request) {
		if ($product = $this->buyableFromRequest()) {
			$quantity = (int) $request->getVar('quantity');
			if(!$quantity) $quantity = 1;
			$this->cart->add($product, $quantity, $request->getVars());
		}

		$this->extend('updateAddResponse', $request, $response, $product, $quantity);
		return $response ? $response : self::direct();
	}

	/**
	 * Action: remove a certain number of items from the cart
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function remove($request) {
		if ($product = $this->buyableFromRequest()) {
			$this->cart->remove($product, $quantity = 1, $request->getVars());
		}

		$this->extend('updateRemoveResponse', $request, $response, $product, $quantity);
		return $response ? $response : self::direct();
	}

	/**
	 * Action: remove all of an item from the cart
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function removeall($request) {
		if ($product = $this->buyableFromRequest()) {
			$this->cart->remove($product, null, $request->getVars());
		}

		$this->extend('updateRemoveAllResponse', $request, $response, $product);
		return $response ? $response : self::direct();
	}


	/**
	 * Action: update the quantity of an item in the cart
	 * @param $request
	 * @return AjaxHTTPResponse|bool
	 */
	public function setquantity($request) {
		$product = $this->buyableFromRequest();
		$quantity = (int) $request->getVar('quantity');
		if ($product) {
			$this->cart->setQuantity($product, $quantity, $request->getVars());
		}

		$this->extend('updateSetQuantityResponse', $request, $response, $product, $quantity);
		return $response ? $response : self::direct();
	}


	/**
	 * Action: clear the cart
	 * @param $request
	 * @return AjaxHTTPResponse|bool
	 */
	public function clear($request) {
		$this->cart->clear();
		$this->extend('updateClearResponse', $request, $response);
		return $response ? $response : self::direct();
	}

	/**
	 * Handle index requests
	 */
	public function index() {
		if($cart = $this->Cart()){
			$this->redirect($cart->CartLink);
			return;
		}elseif($response = ErrorPage::response_for(404)) {
			return $response;
		}
		return $this->httpError(404, _t("ShoppingCart.NOCARTINITIALISED", "no cart initialised"));
	}

	/**
	* Displays order info and cart contents.
	*/
	public function debug() {
		if(Director::isDev() || Permission::check("ADMIN")){
			//TODO: allow specifying a particular id to debug
			Requirements::css(SHOP_DIR."/css/cartdebug.css");
			$order = ShoppingCart::curr();
			$content = ($order) ?
				Debug::text($order) :
				"Cart has not been created yet. Add a product.";
			return array('Content' => $content);
		}
	}

}

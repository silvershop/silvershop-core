<?php

/**
 * ShoppingCart is a session handler that stores information about what products are in a user's cart on the site.
 * 
 * Editing the cart:
 * - Non URL based adding	add_buyable->find_or_make_order_item->add_(new)_item
 * - URL based adding	additem->getNew/ExistingOrderItem->add_(new)_item
 *
 * @author: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @subpackage: control
 *
 **/

class ShoppingCart extends Controller {

	/**
	 *stores the current order - initiated and retrieved with ShoppingCart::current_order()
	 *
	 *@var DataObject(Order)
	 *
	 **/
	protected static $order = null; // for temp caching
		static function set_order(Order $orderObject) {self::$order = $orderObject;}
		static function get_order() {user_error("Use self::current_order() to get order.", E_USER_ERROR);}

	/**
	 * URLSegment used for shopping-cart actions
	 *
	 *@var String
	 *
	 **/
	public static $url_segment = 'shoppingcart';
		static function set_url_segment(string $s) {self::$url_segment = $s;}
		static function get_url_segment() {return self::$url_segment;}

	/**
	 *$cart_session_name: tores both OrderID and Session ID (seperated by a comma)
	 *we need both  Session ID and Order ID here because
	 * (a) you may have several orders with one session ID -
	 * (b) you may have an order that is not in the current session....
	 * @var String
	 **/
	protected static $cartid_session_name = 'shoppingcartid';
		public static function set_cartid_session_name(string $s) {self::$cartid_session_name = $s;}
		public static function get_cartid_session_name() {return self::$cartid_session_name;}

	/**
	 *	used for allowing certian url parameters to be applied to orderitems
	 *	eg: ?Color=red will set OrderItem color to 'red'
	 *	name - defaultvalue (needed for default orderitems)
	 *
	 *	array(
	 *		'Color' => 'Red' //default to red
	 *	)
	 *
	*/
	protected static $default_param_filters = array();
		static function set_default_param_filters(array $a){self::$default_param_filters = $a;}
		static function add_default_param_filters(array $a){self::$default_param_filters = array_merge(self::$default_param_filters,$a);}
		static function get_default_param_filters(){return self::$default_param_filters;}

	/**
	 * $response_class is the name of the class that provides the repsonse to "actions" called in the shopping cart.
	 *
	 *@var String
	 **/
	protected static $response_class = "CartResponse";
		public static function set_response_class(string $s) {self::$url_segment = $s;}
		public static function get_response_class() {return self::$url_segment;}

	/**
	 * $template_id_prefix is a prefix to all HTML IDs referred to in the shopping cart
	 * e.g. CartCellID can become MyCartCellID by setting the template_id_prefix to "My"
	 * The IDs are used for setting values in the HTML using the AJAX method with
	 * the CartResponse providing the DATA (JSON).
	 *
	 *@var String
	 **/
	protected static $template_id_prefix = "";
		public static function set_template_id_prefix(string $s) {self::$template_id_prefix = $s;}
		public static function get_template_id_prefix() {return self::$template_id_prefix;}

	/**
	 * Add additional JS functionality to your shopping cart.  The default is to add AJAX "add" / "remove" from cart methods
	 *
	 *
	 * @var Array
	 **/
	protected static $additional_javascript_requirements = array("ecommerce/javascript/EcomAjaxCart.js");
		public static function set_additional_javascript_requirements(Array $a) {self::$additional_javascript_requirements = $a;}
		public static function get_additional_javascript_requirements() {return self::$additional_javascript_requirements;}


	public static function get_country_setting_index(){
		return "countrysettingindex";
	}

	public static $allowed_actions = array (
		'additem',
		'incrementitem',
		'decrementitem',
		'removeitem',
		'removeallitem',
		'removemodifier',
		'addmodifier',
		'setcountry',
		'setquantityitem',
		'clearcartandlogout',
		'clear',
		'numberofitemsincart',
		'showcart',
		'loadorder',
		'copyorder',
		'debug' => 'ADMIN'
	);

/*******************************************************
	* COUNTRY MANAGEMENT
*******************************************************/

	/**
	 * Sets the country for the order...
	 *@param $s CountryCode (e.g. NZ)
	 **/
	static function set_country(string $s) {
		if(EcommerceCountry::country_code_allowed($s)) {
			Session::set(self::get_country_setting_index(), $s);
			$member = Member::currentUser();
			//check if the member has a country
			if($o = self::current_order()) {
				$o->SetCountry($s);
			}
		}
	}



/*******************************************************
	 * STARTUP
*******************************************************/

	function init() {
		parent::init();
		self::$order = self::current_order();
	}

	/**
	* load_order:
	*@return Order if found, otherwise null. IMPORTANT
	**/
	public static function load_order($orderID, $memberID = 0) {
		if(!$memberID) {
			$memberID = Member::currentUserID();
		}
		if($memberID) {
			$order = Order::get_by_id_if_can_view($orderID);
			if($order && $order->MemberID == $memberID ) {
				self::$order = $order;
				self::initialise_new_order();
				return self::current_order();
			}
		}
		return null;
	}

	/**
	 * NOTE: tried to copy part to the Order Class - but that was not much of a go-er.
	 *@return DataObject(Order)
	 **/
	public static function copy_order($oldOrderID) {
		$oldOrder = Order::get_by_id_if_can_view($oldOrderID);
		if(!$oldOrder) {
			user_error("Could not find old order", E_USER_NOTICE);
		}
		else {
			$newOrder = new Order();
			//for later use...
			$fieldList = array_keys(DB::fieldList("Order"));
			$newOrder->write();
			self::load_order($newOrder->ID, $oldOrder->MemberID);
			self::$order = $newOrder;
			self::initialise_new_order();
			$items = DataObject::get("OrderItem", "\"OrderID\" = ".$oldOrder->ID);
			if($items) {
				foreach($items as $item) {
					$buyable = $item->Buyable($current = true);
					if($buyable->canPurchase()) {
						self::add_buyable($buyable, $item->Quantity);
					}
				}
			}
			$newOrder->write();
			return $newOrder;
		}
	}

	/**
	 * This is THE pivotal method of the ShoppingCart.
	 * Anytime you want to display (parts) of the order, you should use this method to retrieve the Order.
	 * It will provide the Order that is currently stored in the Session and it will also add additional stuff to it
	 * that is relevant to displaying it.
	 *
 	 *@return DataObject(Order)
	 **/
	public static function current_order() {
		if (!self::$order) {
			//find order by id saved to session (allows logging out and retaining cart contents)
			$cartID = Session::get(self::get_cartid_session_name().".OrderAndSessionID");
			//we need both  Session ID and Order ID here because (a) you may have several orders with one session ID - (b) you may have an order that is not in the current session....
			$cartIDParts = explode(",", $cartID);
			if($cartIDParts && is_array($cartIDParts) && count($cartIDParts) == 2) {
				self::$order = DataObject::get_one('Order',"\"Order\".\"ID\" = '".intval($cartIDParts[0])."' AND \"Order\".\"SessionID\" = '".intval($cartIDParts[1])."'");
			}
			if(!self::$order ){
				self::$order = new Order();
				self::initialise_new_order();
			}
			self::add_requirements();
			self::$order->calculateModifiers();
		}
		//add shopping cart items
		self::add_template_ids_and_message();
		return self::$order;
	}

	/**
	 * removes the current order from session
	 **/
	public function clear_order_from_shopping_cart() {
		Session::set(self::get_cartid_session_name().".OrderAndSessionID",null);
	}

	/**
	 * Stores an order into session and associates the current Member with it (if any).
	 **/
	protected static function initialise_new_order() {
		self::$order->SessionID = session_id();
		self::$order->MemberID = Member::currentUserID();
		self::$order->write();
		//we need both  Session ID and Order ID here because (a) you may have several orders with one session ID - (b) you may have an order that is not in the current session....
		Session::set(self::get_cartid_session_name()."OrderAndSessionID",self::$order->ID.",".self::$order->SessionID);

		//see issue: 140
		CartCleanupTask::run_on_demand();
	}


	public static function add_requirements() {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript('ecommerce/javascript/EcomCart.js');
		$array = self::get_additional_javascript_requirements();
		if(count($array)) {
			foreach($array as $fileName) {
				Requirements::javascript($fileName);
			}
		}
		Requirements::themedCSS("Cart");
	}


/*******************************************************
	 * CONTROLLER LINKS - all return STRINGS!
*******************************************************/

	function Link($action = null){
		$action = ($action)? "/$action/" : "";
		return self::$url_segment.$action;
	}

	static function add_item_link($buyableID, $className = "OrderItem", $parameters = array()) {
		return self::$url_segment.'/additem/'.$buyableID."/".self::order_item_class_name($className).self::params_to_get_string($parameters);
	}

	static function increment_item_link($buyableID, $className = "OrderItem", $parameters = array()) {
		return self::$url_segment.'/incrementitem/'.$buyableID."/".self::order_item_class_name($className).self::params_to_get_string($parameters);
	}

	static function decrement_item_link($buyableID, $className = "OrderItem", $parameters = array()) {
		return self::$url_segment.'/decrementitem/'.$buyableID."/".self::order_item_class_name($className).self::params_to_get_string($parameters);
	}

	static function remove_item_link($buyableID, $className = "OrderItem", $parameters = array()) {
		return self::$url_segment.'/removeitem/'.$buyableID."/".self::order_item_class_name($className).self::params_to_get_string($parameters);
	}

	static function remove_all_item_link($buyableID, $className = "OrderItem", $parameters = array()) {
		return self::$url_segment.'/removeallitem/'.$buyableID."/".self::order_item_class_name($className).self::params_to_get_string($parameters);
	}

	static function set_quantity_item_link($buyableID, $className = "OrderItem", $parameters = array()) {
		return self::$url_segment.'/setquantityitem/'.$buyableID."/".self::order_item_class_name($className).self::params_to_get_string($parameters);
	}

	static function add_modifier_link($modifierID = 0, $className = "OrderModifier") {
		return self::$url_segment.'/addmodifier/'.$modifierID."/".self::order_modifier_class_name($className);
	}

	static function remove_modifier_link($modifierID, $className = "OrderModifier") {
		return self::$url_segment.'/removemodifier/'.$modifierID."/".self::order_modifier_class_name($className);
	}

	static function clear_cart_and_logout_link() {
		return self::$url_segment.'/clearcartandlogout/';
	}

	static function set_country_link() {
		return self::$url_segment.'/setcountry/';
	}

	/** helper function for appending variation id */
	protected static function variation_link($variationid) {
		user_error("This function is now outdated and we should use classname link instead!", E_USER_ERROR);
	}

/*******************************************************
	 * ORDER ITEM  AND MODIFIER INFORMATION
*******************************************************/

	/**
	 *@return String
	 **/
	protected static function order_item_class_name($className) {
		if(!ClassInfo::exists($className)) {
			user_error("ShoppingCart::order_item_class_name ... $className does not exist", E_USER_ERROR);
		}
		if(in_array($className, array("OrderItem", "OrderAttribute"))) {
			user_error("ShoppingCart::order_item_class_name ... $className should be a subclassed", E_USER_NOTICE);
			return $className;
		}
		if(ClassInfo::is_subclass_of($className, "OrderItem")) {
			//do nothing
			$length = strlen(Buyable::get_order_item_class_name_post_fix()) * -1;
			if(substr($className, $length) != Buyable::get_order_item_class_name_post_fix()) {
				user_error("ShoppingCart::order_item_class_name, $className should end in '".Buyable::get_order_item_class_name_post_fix()."'", E_USER_ERROR);
			}
		}
		elseif(ClassInfo::is_subclass_of($className, "DataObject")) {
			$className .= Buyable::get_order_item_class_name_post_fix();
			return self::order_item_class_name($className);
		}
		return $className;
	}

	/**
	 *@return String
	 **/
	protected static function buyable_class_name($orderItemClassName) {
		return str_replace(Buyable::get_order_item_class_name_post_fix(), "", self::order_item_class_name($orderItemClassName));
	}

	//modifiers

	/**
	 *@return String
	 **/
	protected static function order_modifier_class_name($className) {
		if(!ClassInfo::exists($className)) {
			user_error("ShoppingCart::order_modifier_class_name ... $className does not exist", E_USER_ERROR);
		}
		if(in_array($className, array("OrderAttribute", "OrderModifier"))) {
			user_error("ShoppingCart::order_modifier_class_name ... $className should be a subclassed", E_USER_NOTICE);
			return $className;
		}
		if(ClassInfo::is_subclass_of($className, "OrderModifier")) {
			//do nothing
		}
		else {
			user_error("ShoppingCart::order_modifier_class_name ... $className should be a subclass of OrderModifier", E_USER_ERROR);
		}
		return $className;
	}

/*******************************************************
	 * RETRIEVE INFORMATION
*******************************************************/

	/**
	 *@return DataObject (Order)
	 **/
	function Cart() {
		return self::current_order();
	}

	/**
	 *@return boolean
	 **/
	static function has_items() {
		return self::current_order()->Items() != null;
	}

	/**
	 *@return DataObjectSet (OrderItem)
	 **/
	static function get_items($filter = null) {
		return self::current_order()->Items($filter);
	}

	/**
	 *@return Boolean
	 **/
	static function has_modifiers() {
		return self::get_modifiers() != null;
	}

	/**
	 *@return DataObjectSet (OrderItemModifiers)
	 **/
	static function get_modifiers() {
		return self::current_order()->Modifiers();
	}

	/**
	 * Get OrderItem according to product id, and coorresponding parameter filter.
	 *
	 *@return OrderItem (ONE!)
	 **/
	static function get_order_item_by_buyableid($buyableID, $orderItemClassName = "OrderItem", $parameters = null ) {
		if(!ClassInfo::is_subclass_of($orderItemClassName, "OrderItem")) {
			user_error("$orderItemClassName needs to be a subclass of OrderItem", E_USER_WARNING);
		}
		$filter = self::turn_params_into_sql($parameters = null);
		$order = self::current_order();
		$filterString = ($filter && trim($filter) != "") ? " AND $filter" : "";
		// NOTE: MUST HAVE THE EXACT CLASSNAME !!!!! THEREFORE INCLUDED IN WHERE PHRASE
		return DataObject::get_one($orderItemClassName, "\"ClassName\" = '".$orderItemClassName."' AND \"OrderID\" = ".$order->ID." AND \"BuyableID\" = ".$buyableID." ". $filterString);
	}

/*******************************************************
	 * NON ITEM/MODIFIER INFORMATION
*******************************************************/

	static function uses_different_shipping_address(){
		return self::current_order()->UseShippingAddress;
	}

	static function set_uses_different_shipping_address($b = true){
		$order = self::current_order();
		$order->UseShippingAddress = $b;
		$order->write();
	}


/*******************************************************
	 * STATIC FUNCTIONS
*******************************************************/

	/**
	 * Either update or create OrderItem in ShoppingCart.
	 */
	public static function add_new_item(OrderItem $newOrderItem, $quantity = 1) {
		//what happens if it has already been added???
		$newOrderItem->Quantity = $quantity;
		$newOrderItem->write();
		self::current_order()->Attributes()->add($newOrderItem);
	}

	/**
	 * Add QTY to an existing OrderItem to session
	 */
	public static function increment_item($existingOrderItem, $quantity = 1) {
		//what happens if the item doe not actually exists?
		if($existingOrderItem->ID) {
			$existingOrderItem->Quantity += $quantity;
			$existingOrderItem->write();
		}
		else {
			user_error("Item has not been saved yet", E_USER_WARNING);
		}
	}
	/**
	 * reduce QTY to an existing OrderItem to session
	 */
	public static function decrement_item($existingOrderItem, $quantity = 1) {
		//what happens if the item doe not actually exists?
		if($existingOrderItem->ID) {
			$existingOrderItem->Quantity -= $quantity;
			$existingOrderItem->write();
		}
		else {
			user_error("Item has not been saved yet", E_USER_WARNING);
		}
	}

	/**
	 * Update quantity of an OrderItem in the session
	 */
	public static function set_quantity_item($existingOrderItem, $quantity) {
		if ($existingOrderItem) {
			$existingOrderItem->Quantity = $quantity;
			$existingOrderItem->write();
		}
	}

	/**
	 * Reduce quantity of an orderItem, or completely remove
	 */
	public static function remove_item($existingOrderItem, $quantityToReduceBy = 1) {
		if ($existingOrderItem) {
			if ($quantityToReduceBy >= $existingOrderItem->Quantity) {
				$existingOrderItem->delete();
				$existingOrderItem->destroy();
			}
			else {
				self::decrement_item($existingOrderItem, $quantityToReduceBy);
			}
		}
	}

	public static function remove_all_item($existingOrderItem) {
		if($existingOrderItem){
			$existingOrderItem->delete();
			$existingOrderItem->destroy();
		}
	}

	public static function remove_all_items() {
		$items = self::get_items();
		return self::remove_many_order_attributes($items);
	}

	public static function remove_all_modifiers() {
		$items = self::get_modifiers();
		return self::remove_many_order_attributes($items);
	}

	protected static function remove_many_order_attributes($items) {
		self::current_order()->Attributes()->removeMany($items);
		if($items) {
			foreach($items as $item) {
				$item->delete();
				$item->destroy();
			}
		}
	}

	/**
	 * adds a "product" to the cart.
	 *@param $buyable DataObject ( a buyable DataObject, such as a product or a product variation)
	 *@param $quantity Integer
	 *@param $parameters Array | Null
	 *@return DataObject (OrderItem)
	 **/
	public static function add_buyable($buyable, $quantity = 1, $parameters = null){
		$orderItem = null;
		if(!$buyable) {
			user_error("No buyable was provided to add", E_USER_NOTICE);
			return null;
		}
		$orderItem = self::find_or_make_order_item($buyable, $parameters = null);
		if($orderItem) {
			if($orderItem->ID){
				self::increment_item($orderItem, $quantity);
			}
			else{
				self::add_new_item($orderItem, $quantity);
			}
		}
		return $orderItem;
	}

	/**
	 *@return DataObject (OrderItem)
	 **/
	protected static function find_or_make_order_item($buyable, $parameters = null){
		if($orderItem = self::get_order_item_by_buyableid($buyable->ID,$buyable->classNameForOrderItem())){
			//do nothing
		}
		else {
			$orderItem = self::create_order_item($buyable, 1, $parameters = null);
		}
		return $orderItem;
	}

	/**
	 *@return DataObject (OrderItem)
	 **/
	protected static function create_order_item($buyable,$quantity = 1, $parameters = null){
		$orderItem = null;
		if($buyable && $buyable->canPurchase()) {
			$classNameForOrderItem = $buyable->classNameForOrderItem();
			$orderItem = new $classNameForOrderItem();
			$orderItem->addBuyableToOrderItem($buyable, $quantity);
		}
		if($orderItem) {
			//set extra parameters
			if($orderItem instanceof OrderItem && is_array($parameters)){
				$defaultParamFilters = self::get_default_param_filters();
				foreach($defaultParamFilters as $param => $defaultvalue){
					$v = (isset($parameters[$param])) ? Convert::raw2sql($parameters[$param]) : $defaultvalue;
					//how does this get saved in database? should we check if field exists?
					$orderItem->$param = $v;
				}
			}
		}
		else {
			user_error("product is not for sale or buyable (".$buyable->Title().") does not exists.", E_USER_NOTICE);
		}
		return $orderItem;
	}

	// Modifiers management

	public static function add_new_modifier(OrderModifier $modifier) {
		user_error("this function has been depecriated", E_USER_ERROR);
	}


	public static function remove_modifier($modifier) {
		$modifier->HasBeenRemoved = 1;
		$modifier->write();
	}


/*******************************************************
	 * URL ACTIONS
*******************************************************/


	/**
	 *@return String (message)
	 **/
	function additem($request) {
		if ($request->param('ID')) {
			if($orderItem = $this->getExistingOrderItemFromURL()) {
				if($request->param("Action") == "decrementitem" ) {
					ShoppingCart::decrement_item($orderItem, 1);
					return $this->returnMessage("success",_t("ShoppingCart.SUPERFLUOUSITEMREMOVED", "Superfluous item removed"));
				}
				else {
					ShoppingCart::increment_item($orderItem, 1);
					return $this->returnMessage("success",_t("ShoppingCart.EXTRAITEMADDED", "Extra item added"));
				}
			}
			else {
				if($orderItem = $this->getNewOrderItemFromURL()) {
					ShoppingCart::add_new_item($orderItem, 1);
					return $this->returnMessage("success",_t("ShoppingCart.EXTRAITEMADDED", "Item added"));
				}
			}
		}
		return $this->returnMessage("failure",_t("ShoppingCart.ITEMCOULDNOTBEADDED", "Item could not be added"));
	}

	/**
	 *@return String (message)
	 **/
	function incrementitem($request) {
		return $this->additem($request);
	}

	/**
	 *@return String (message)
	 **/
	function decrementitem($request) {
		return $this->additem($request);
	}

	/**
	 *@return String (message)
	 **/
	function removeitem($request) {
		if ($orderItem = $this->getExistingOrderItemFromURL()) {
			ShoppingCart::remove_item($orderItem);
			return $this->returnMessage("success",_t("ShoppingCart.ITEMREMOVED", "Item removed."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.ITEMCOULDNOTBEFOUNDINCART", "Item could not found in cart."));
	}

	function removeallitem($request) {
		if ($orderItem = $this->getExistingOrderItemFromURL()) {
			ShoppingCart::remove_all_item($orderItem);
			return $this->returnMessage("success",_t("ShoppingCart.ITEMCOMPLETELYREMOVED", "Item completely removed."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.ITEMCOULDNOTBEFOUNDINCART", "Item could not found in cart."));
	}


	/**
	 * Ajax method to set an item quantity
	 *
	 *@return String (message)
	 **/
	function setquantityitem($request) {
		$quantity = $request->getVar('quantity');
		if (is_numeric($quantity) && $quantity == floatval($quantity)) {
			$orderItem = $this->getExistingOrderItemFromURL();
			if(!$orderItem){
				$newOrderItem = $this->getNewOrderItemFromURL();
				self::add_new_item($newOrderItem, $quantity);
			}
			else{
				ShoppingCart::set_quantity_item($orderItem, $quantity);
			}
			return $this->returnMessage("success",_t("ShoppingCart.QUANTITYSET", "Quantity set."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.QUANTITYNOTNUMERIC", "Quantity provided is not numeric."));
	}

	/**
	 * add specified modifier, if allowed
	 */
	function addmodifier($request) {
		$modifierId = intval($request->param('ID'));
		if(!$modifierId) {
			$className = $request->param('OtherID');
			if(class_exists($className)) {
				$modifier = new $className();
				if(!($modifier instanceof OrderModifier)) {
					$modifier = null;
				}
				else {
					$modifier->init();
				}
			}
		}
		else {
			$modifier = DataObject::get_by_id("OrderModifier", $modifierId);
		}
		if($modifier) {
			if($modifier->ID == $modifierId) {
				$modifier->HasBeenRemoved = 0;
			}
			$modifier->runUpdate();
			return $this->returnMessage("success",_t("ShoppingCart.MODIFIERADDED", "Order Extra added."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.MODIFIERNOTADDED", "Could not add Order Extra."));
	}

	/**
	 * Removes specified modifier, if allowed
	 *
	 *@return String (message)
	 **/
	function removemodifier($request) {
		$modifierId = intval($request->param('ID'));
		$modifier = DataObject::get_by_id("OrderModifier", $modifierId);
		if ($modifier && $modifier->CanBeRemoved()){
			ShoppingCart::remove_modifier($modifier);
			return $this->returnMessage("success",_t("ShoppingCart.MODIFIERREMOVED", "Removed extra."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.MODIFIERNOTREMOVED", "Could not remove extra."));
	}


	/**
	 *@return String (message)
	 **/
	function setcountry($request) {
		$countryCode = $request->param('ID');
		if($countryCode) {
			//set_country will check if the country code is actually allowed....
			ShoppingCart::set_country($countryCode);
			return $this->returnMessage("success",_t("ShoppingCart.COUNTRYUPDATED", "Country updated."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.COUNTRYCOULDNOTBEUPDATED", "Country not be updated."));
	}

	/**
	 * Clears the cart
	 * It disconnects the current cart from the user session.
	 */
	function clear($request = null) {
		self::current_order()->SessionID = null;
		self::current_order()->write();
		self::remove_all_items();
		self::$order = null;

		//redirect back or send ajax only if called via http request.
		//This check allows this function to be called from elsewhere in the system.
		if($request instanceof SS_HTTPRequest){
			return $this->returnMessage("success",_t("ShoppingCart.CARTCLEARED", "Cart cleared."));
		}
	}

	/**
	 * Log out and clear cart
	 *
	 *@return String (message)
	 **/
	function clearcartandlogout($request = null) {
		$this->clear($request);
		if($member = Member::currentUser()) {
			$member->logout();
		}
		return $this->returnMessage("failure",_t("ShoppingCart.CARTCLEAREDANDLOGGEDOUT", "Cart cleared and you have been logged out."));
	}

	/**
	 * return number of items in cart
	 *@return integer
	 **/
	function numberofitemsincart() {
		$cart = self::current_order();
		if($cart) {
			return $cart->TotalItems();
		}
		return 0;
	}

	/**
	 * return cart for ajax call
	 *@return HTML
	 */
	function showcart($request) {
		return $this->renderWith("AjaxSimpleCart");
	}

	/**
	 *@return String (message)
	 **/
	function loadorder($request) {
		$orderID = Director::urlParam('ID');
		if($orderID == intval($orderID)) {
			if(self::load_order($orderID)) {
				return $this->returnMessage("success", _t("ShoppingCart.ORDERLOADEDSUCCESSFULLY", "Order has been loaded."));
			}
		}
		return $this->returnMessage("failure", _t("ShoppingCart.ORDERNOTLOADEDSUCCESSFULLY", "Order could not be loaded."));
	}

	/**
	 *@return String (message)
	 **/
	function copyorder($request) {
		$orderID = Director::urlParam('ID');
		if($orderID == intval($orderID)) {
			if(self::copy_order(intval($orderID))) {
				return $this->returnMessage("success", _t("ShoppingCart.ORDERCREATEDSUCCESSFULLY", "Order has been created."));
			}
		}
		return $this->returnMessage("failure", _t("ShoppingCart.ORDERNOTCREATEDSUCCESSFULLY", "Order could not be created."));
	}

	/**
	 * Sets appropriate status, and message and redirects or returns appropriately.
	 * @return JSON or redirects back
	 **/

	protected function returnMessage($status = "success",$message = null) {
		return self::return_message($status = "success",$message);
	}

	public static function return_message($status = "success",$message = null){
		if(Director::is_ajax()){
			$obj = new self::${response_class}();
			return $obj->ReturnCartData($status, $message);
		}
		else {
			Session::set(self::get_cartid_session_name().".Message", $message);
			Session::set(self::get_cartid_session_name().".Status", $status);
			Director::redirectBack();
			return;
		}
	}

/*******************************************************
	 * URL DECODING AND FILTERING
*******************************************************/

	/**
	 * Creates the appropriate string parameters for links from array
	 *
	 * Produces string such as: MyParam%3D11%26OtherParam%3D1
	 *     ...which decodes to: MyParam=11&OtherParam=1
	 *
	 * you will need to decode the url with javascript before using it.
	 *
	 *@return String (URLSegment)
	 */
	protected static function params_to_get_string($array){
		if($array & count($array > 0)){
			array_walk($array , create_function('&$v,$k', '$v = $k."=".$v ;'));
			return "/?".implode("&",$array);
		}
		return "/";
	}


	/**
	 * Creates new order item based on url parameters
	 *@return DataObject (OrderItem)
	 */
	protected function getNewOrderItemFromURL(){
		$request = $this->getRequest();
		$orderitem = null;
		$buyableID = intval($request->param('ID'));
		//create order item
		if(is_numeric($buyableID)) {
			$buyableClassName = self::buyable_class_name($request->param('OtherID'));
			if($buyableClassName) {
				$buyable = null;
				/*
				if(Object::has_extension($buyableClassName,'Versioned') && singleton($buyableClassName)->hasVersionField('Live')){ //only 'Live' versions should be used for versioned products
					die("A");
					$buyable = Versioned::get_one_by_stage($buyableClassName,'Live', '"'.$buyableClassName.'_Live"."ID" = '.$buyableID);
				}
			*/
				$buyable = DataObject::get_by_id($buyableClassName, $buyableID);
				if ($buyable ) {
					if($buyable->canPurchase()) {
						$orderItemClassName = self::order_item_class_name($buyable->ClassName);
						$orderitem = new $orderItemClassName();
						$orderitem->addBuyableToOrderItem($buyable,1);
					}
					else {
						user_error($buyable->Title." is not for sale!", E_USER_ERROR);
					}
				}
				else {
					user_error("Buyable was not provided", E_USER_ERROR);
				}
			}
			else {
				user_error("no itemClassName ($buyableClassName) provided for item to be added", E_USER_ERROR);
			}
		}
		else {
			user_error("no id provided for item to be added - should be a URL parameter", E_USER_ERROR);
		}
		//set extra parameters
		if($orderitem instanceof OrderItem){
			$defaultParamFilters = self::get_default_param_filters();
			foreach($defaultParamFilters as $param => $defaultvalue){
				$v = ($request->getVar($param)) ? Convert::raw2sql($request->getVar($param)) : $defaultvalue;
				$orderitem->$param = $v;
			}
		}
		return $orderitem;
	}


	/**
	 * Get item according to a filter.
	 *@return DataObject(OrderItem)
	 */
	protected function getExistingOrderItemFromURL() {
		$filter = $this->urlFilter();
		$order = self::current_order();
		if($filter) {
			$filterString = " AND ($filter)";
		}
		return  DataObject::get_one('OrderItem', "\"OrderID\" = $order->ID $filterString");
	}


	/**
	 * Gets a SQL filter based on array of parameters.
	 *
	 * 	 Returns default filter if none provided,
	 *	 otherwise it updates default filter with passed parameters
	 *@return String (SQL where statement)
	 */
	protected static function turn_params_into_sql($params = array()){
		$defaultParamFilters = self::get_default_param_filters();
		if(!count($defaultParamFilters)) {
			return ""; //no use for this if there are not parameters defined
		}
		$outputArray = array();
		foreach($defaultParamFilters as $field => $value){
			if(isset($params[$field])){
				//see issue 147
				$defaultParamFilters[$field] = Convert::raw2sql($params[$field]);
			}
			$outputarray[$field] = "\"".$field."\" = ".$defaultParamFilters[$field];
		}
		if(count($outputArray)) {
			return implode(" AND ",$outputArray);
		}
	}

	/**
	 * Gets a filter based on urlParameters
	 *@return String (SQL where statement)
	 */
	protected function urlFilter(){
		$result = '';
		$request = $this->getRequest();
		$orderItemClassName = self::order_item_class_name($request->param('OtherID'));
		$buyableClassName = self::buyable_class_name($orderItemClassName);
		$selection = array(
			"\"BuyableID\" = ".$request->param('ID')
		);
		if(ClassInfo::is_subclass_of($request->param('OtherID'), "OrderAttribute")){
			$selection[] = "\"ClassName\" = '".$orderItemClassName."'";
		}

		$filter = self::turn_params_into_sql($request->getVars());
		if( $filter ){
			$result = implode(" AND ",array_merge($selection,array($filter)));
		}
		else {
			$result = implode(" AND ",$selection);
		}
		return $result;
	}


/*******************************************************
	 * ORDER TEMPLATE STUFF
*******************************************************/


	/**
	 * For use in the templates as ID
	 *@return String
	 **/
	protected static function add_template_ids_and_message() {
		if($message = Session::get(self::get_cartid_session_name().".Message")) {
			self::$order->CartStatusMessage = $message;
			if($className = Session::get(self::get_cartid_session_name().".Status")) {
				self::$order->CartStatusClass = $className;
			}
		}

		Session::clear(self::get_cartid_session_name());
		
		self::$order->TableMessageID = self::$template_id_prefix.'Table_Order_Message';
		self::$order->TableSubTotalID = self::$template_id_prefix.'Table_Order_SubTotal';
		self::$order->TableTotalID = self::$template_id_prefix.'Table_Order_Total';
		self::$order->OrderForm_OrderForm_AmountID = self::$template_id_prefix.'OrderForm_OrderForm_Amount';
		self::$order->CartSubTotalID = self::$template_id_prefix.'Cart_Order_SubTotal';
		self::$order->CartTotalID = self::$template_id_prefix.'Cart_Order_Total';
		
	}

	/**
	 *
	 *@return Array (for use in AJAX for JSON)
	 **/
	static function update_for_ajax(array &$js, $message = '', $status = 'Success') {
		$subTotal = self::$order->SubTotalAsCurrencyObject()->Nice();
		$total = self::$order->TotalAsCurrencyObject()->Nice();
		$js[] = array('id' => self::$order->TableSubTotalID, 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => self::$order->TableTotalID, 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => self::$order->OrderForm_OrderForm_AmountID, 'parameter' => 'innerHTML', 'value' => $total);
		$js[] = array('id' => self::$order->CartSubTotalID, 'parameter' => 'innerHTML', 'value' => $subTotal);
		$js[] = array('id' => self::$order->CartTotalID, 'parameter' => 'innerHTML', 'value' => $total);
		if($message) {
			$js[] = array(
				"id" => self::$order->TableMessageID,
				"parameter" => "innerHTML",
				"value" => $message,
				"isOrderMessage" => true,
				"messageClass" => $status
			);
			$js[] = array(
				"id" =>  self::$order->TableMessageID,
				"parameter" => "hide",
				"value" => 0
			);
		}
		else {
			$js[] = array(
				"id" => self::$order->TableMessageID,
				"parameter" => "hide",
				"value" => 1
			);
		}

	}



/*******************************************************
	 * DEBUG
*******************************************************/


	function debug() {
		Debug::show(ShoppingCart::current_order());
	}



}

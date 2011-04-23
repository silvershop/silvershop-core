<?php

/**
 * ShoppingCart is a session handler that stores
 * information about what products are in a user's
 * cart on the site.
 *
 * @package ecommerce
 * @Description
 ** Non URL based adding	add_buyable->find_or_make_order_item->add_(new)_item
 ** URL based adding	additem->getNew/ExistingOrderItem->add_(new)_item
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class ShoppingCart extends Controller {

	//EXPLAIN: what is the reasoning behind having getters and setters for every static variable? why not just add them when needed?
	public static $url_segment = 'shoppingcart';
		static function set_url_segment($v) {self::$url_segment = $v;}
		static function get_url_segment() {return self::$url_segment;}

	protected static $order = null; // for temp caching
		static function set_order(Order $v) {self::$order = $v;}
		static function get_order() {user_error("Use self::current_order() to get order.", E_USER_ERROR);}

	protected static $cartid_session_name = 'shoppingcartid';
		public static function set_cartid_session_name($v) {self::$cartid_session_name = $v;}
		public static function get_cartid_session_name() {return self::$cartid_session_name;}

	protected static $response_class = "CartResponse";
		public static function set_response_class($v) {self::$url_segment = $v;}
		public static function get_response_class() {return self::$url_segment;}

	protected static $ajaxify_cart = false;
		public static function set_ajaxify_cart($v) {self::$ajaxify_cart = $v;}
		public static function get_ajaxify_cart() {return self::$ajaxify_cart;}

	static $allowed_actions = array (
		'additem',
		'incrementitem',
		'decrementitem',
		'removeitem',
		'removeallitem',
		'removemodifier',
		'setcountry',
		'setquantityitem',
		'clear',
		'numberofitemsincart',
		'showcart',
		'loadorder',
		'copyorder',
		'debug' => 'SHOP_ADMIN'
	);


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
		static function set_default_param_filters($array){self::$default_param_filters = $array;}
		static function add_default_param_filters($array){self::$default_param_filters = array_merge(self::$default_param_filters,$array);}
		static function get_default_param_filters(){return self::$default_param_filters;}

	protected static $shopping_cart_message_index = 'ShoppingCartMessage';
		static function set_shopping_cart_message_index($v) {self::$shopping_cart_message_index = $v;}
		static function get_shopping_cart_message_index() {return self::$shopping_cart_message_index;}



/*******************************************************
   * COUNTRY MANAGEMENT
  * NOTE THAT WE GET THE COUNTRY FROM MULTIPLE SOURCES!
*******************************************************/

	protected static $country_setting_index = 'ShoppingCartCountry';
		static function set_country_setting_index($v) {self::$country_setting_index = $v;}
		static function get_country_setting_index() {return self::$country_setting_index;}

	static function set_country($countryCode) {
		Session::set(self::get_country_setting_index(), $countryCode);
		$member = Member::currentUser();
		//check if the member has a country
		if($member) {
			$member->Country = $countryCode;
			$member->write();
		}
	}
	static function get_country() {
		//@todo: incorporate allowed countries...
		$countryCode = '';
		//1. fixed country is first
		$countryCode = EcommerceRole::get_fixed_country_code();
		if(!$countryCode) {
			//2. check shipping address
			if($o = self::current_order()) {
				if($o->ShippingAddressID) {
					if($shippingAddress = DataObject::get_by_id("ShippingAddress", $o->ShippingAddressID)) {
						$countryCode = $shippingAddress->ShippingCountry;
					}
				}
			}
			//3. check member
			if(!$countryCode) {
				$member = Member::currentUser();
				if($member && $member->Country) {
					$countryCode = $member->Country;
				}
				//4. check session - NOTE: session saves to member + shipping address
				if(!$countryCode) {
					$countryCode = Session::get(self::get_country_setting_index());
					//5. check GEOIP information
					if(!$countryCode) {
						$countryCode = Geoip::visitor_country();
						//6. check default country....
						if(!$countryCode) {
							$countryCode = Geoip::$default_country_code;
							//7. check default countries from ecommerce...
							if(!$countryCode) {
								$a = EcommerceRole::get_allowed_country_codes();
								if(is_array($a) && count($a)) {
									$countryCode = array_shift($a);
								}
							}
						}
					}
				}
			}
		}
		return $countryCode;
	}

	static function remove_country() {Session::clear(self::get_country_setting_index());}


/*******************************************************
   * CLEARING OLD ORDERS
*******************************************************/

	protected static $clear_days = 90;
		function set_clear_days($days = 90){self::$clear_days = $days;}
		function get_clear_days(){return self::$clear_days;}

	protected static $never_delete_if_linked_to_member = false;
		function set_never_delete_if_linked_to_member($b){self::$never_delete_if_linked_to_member = $b;}
		function get_never_delete_if_linked_to_member(){return self::$never_delete_if_linked_to_member;}


/*******************************************************
   * DELETE OLD SHOPPING CARTS
*******************************************************/

	public static function delete_old_carts(){
		$time = date('Y-m-d H:i:s', strtotime("-".self::$clear_days." days"));
		$generalWhere = "\"StatusID\" = ".OrderStep::get_status_id_from_code("CREATED")." AND \"LastEdited\" < '$time'";
		if(self::$never_delete_if_linked_to_member) {
			$oldcarts = DataObject::get('Order',$generalWhere." AND \"Member\".\"ID\" IS NULL", $sort = "", $join = "LEFT JOIN \"Member\" ON \"Member\".\"ID\" = \"Order\".\"MemberID\" ");
		}
		else {
			$oldcarts = DataObject::get('Order',$generalWhere);
		}
		if($oldcarts){
			foreach($oldcarts as $cart){
				$cart->delete();
				$cart->destroy();
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
			$order = DataObject::get_by_id('Order', $orderID);
			if($order && $order->MemberID == $memberID ) {
				self::$order = $order;
				self::initialise_new_order();
				return self::current_order();
			}
		}
		return null;
	}
	
	
	//EXPLAIN: what is this for, why  is it not on Order?
	public static function copy_order($oldOrderID) {
		$oldOrder = DataObject::get_by_id("Order", $oldOrderID);
		if(!$oldOrder) {
			user_error("Could not find old order", E_USER_NOTICE);
		}
		else {
			$newOrder = new Order();
			$fieldList = array_keys(DB::fieldList("Order"));
			$newOrder->write();
			self::load_order($newOrder->ID, $oldOrder->MemberID);
			self::$order = $newOrder;
			self::initialise_new_order();
			$items = DataObject::get("OrderItem", "\"OrderID\" = ".$oldOrder->ID);
			if($items) {
				foreach($items as $item) {
					$buyable = $item->Buyable($current = true);
					self::add_buyable($buyable, $item->Quantity);
				}
			}
			$newOrder->write();
			return $newOrder;
		}
	}

	public static function current_order() {
		if (!self::$order) {
			//find order by id saved to session (allows logging out and retaining cart contents)
			$cartID = Session::get(self::$cartid_session_name);
			//TODO: make clear cart on logout optional
			if ($cartID) {
				$cartIDParts = Convert::raw2sql(explode(".", $cartID));
				if(is_array($cartIDParts) && count($cartIDParts) == 2) {
					self::$order = DataObject::get_one(
						'Order',
						"\"Order\".\"ID\" = '".intval($cartIDParts[0])."' AND \"Order\".\"SessionID\" = '".$cartIDParts[1]."'"
					);
				}
			}
			if(!self::$order ){
				//TODO: is this the right time to delete them???
				self::$order = new Order();
				self::initialise_new_order();
			}
			//TODO: re-introduce this because it allows seeing which members don't complete orders
			// // Set the Member relation to this order
			self::add_requirements();
			self::$order->calculateModifiers();
		}
		return self::$order;
	}


	public function clear_order_from_shopping_cart() {
		Session::set(self::$cartid_session_name,null);
	}

	protected static function initialise_new_order() {
		//NOTE: init function includes a write....
		self::$order->init();
		
		//EXPLAIN: why do we store the session id in the session??
		Session::set(self::$cartid_session_name,self::$order->ID.".".session_id()); 
		
		self::delete_old_carts(); //TODO: check how this impacts on performance
	}


	public static function add_requirements() {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript('ecommerce/javascript/Cart.js');
		if(self::get_ajaxify_cart()) {
			Requirements::javascript("ecommerce/javascript/AjaxCart.js");
		}
		Requirements::themedCSS("Cart");
	}


/*******************************************************
   * CONTROLLER LINKS
*******************************************************/

	function Link($action = null){
		$action = ($action)? "/$action/" : ""; 
		return self::$URLSegment.$action;
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

	static function add_modifier_link($modifierID, $className = "OrderModifier") {
		return self::$url_segment.'/addmodifier/'.$modifierID."/".self::order_modifier_class_name($className);
	}

	static function remove_modifier_link($modifierID, $className = "OrderModifier") {
		return self::$url_segment.'/removemodifier/'.$modifierID."/".self::order_modifier_class_name($className);
	}

	static function get_country_link() {
		return self::$url_segment.'/setcountry/';
	}

	/** helper function for appending variation id */
	protected static function variation_link($variationid) {
		user_error("This function is now outdated and we should use classname link instead!", E_USER_ERROR);
	}

/*******************************************************
   * ORDER ITEM  AND MODIFIER INFORMATION
*******************************************************/

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

	protected static function buyable_class_name($orderItemClassName) {
		return str_replace(Buyable::get_order_item_class_name_post_fix(), "", self::order_item_class_name($orderItemClassName));
	}

	//modifiers
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

	function Cart() {
		return self::current_order();
	}

	static function has_items() {
		return self::current_order()->Items() != null;
	}

	static function get_items($filter = null) {
		return self::current_order()->Items($filter);
	}

	static function has_modifiers() {
		return self::get_modifiers() != null;
	}

	static function get_modifiers() {
		return self::current_order()->Modifiers();
	}

	/**
	 * Get OrderItem according to product id, and coorresponding parameter filter.
	 */
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

	static function set_uses_different_shipping_address($use = true){
		$order = self::current_order();
		$order->UseShippingAddress = $use;
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

	public static function add_buyable($buyable,$quantity = 1, $parameters = null){
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
	 * Creates a new order item based on url parameters
	 */
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
		$modifier->Type = "Removed";
		$modifier->write();
	}


/*******************************************************
   * URL ACTIONS
*******************************************************/


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

	function incrementitem($request) {
		return $this->additem($request);
	}

	function decrementitem($request) {
		return $this->additem($request);
	}

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

	function setcountry($request) {
		$countryCode = $request->param('ID');
		if($countryCode && strlen($countryCode) < 4) {
			//to do: check if country exists
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
	 * Ajax method to set an item quantity
	 */
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
		user_error("We are no longer allowing modifiers to be added by the user", E_USER_ERROR);
		return false;
	}
	/**
	 * Removes specified modifier, if allowed
	 */
	function removemodifier($request) {
		$modifierId = intval($request->param('ID'));
		$modifier = DataObject::get_by_id("OrderModifier", $modifierId);
		if ($modifier && $modifier->CanRemove()){
			ShoppingCart::remove_modifier($modifier);
			return $this->returnMessage("success",_t("ShoppingCart.MODIFIERREMOVED", "Removed extra."));
		}
		return $this->returnMessage("failure",_t("ShoppingCart.MODIFIERNOTREMOVED", "Could not remove extra."));
	}

	/**
	 * return number of items in cart
	 */

	function numberofitemsincart() {
		$cart = self::current_order();
		if($cart) {
			return $cart->TotalItems();
		}
		return 0;
	}

	/**
	 * return cart for ajax call
	 */
	function showcart($request) {
		return $this->renderWith("AjaxSimpleCart");
	}

	function loadorder($request) {
		$orderID = Director::urlParam('ID');
		if($orderID == intval($orderID)) {
			if(self::load_order($orderID)) {
				return $this->returnMessage("success", _t("ShoppingCart.ORDERLOADEDSUCCESSFULLY", "Order has been loaded."));
			}
		}
		return $this->returnMessage("failure", _t("ShoppingCart.ORDERNOTLOADEDSUCCESSFULLY", "Order could not be loaded."));
	}

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
	 */

	protected function returnMessage($status = "success",$message = null) {
		return self::return_message($status = "success",$message = null);
	}

	public static function return_message($status = "success",$message = null){
		if(Director::is_ajax()){
			$obj = new self::$response_class();
			return $obj->ReturnCartData($status, $message);
		}
		else {
			Session::set(self::get_shopping_cart_message_index().".Message", $message);
			Session::set(self::get_shopping_cart_message_index().".Status", $status);
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
	 */
	protected static function turn_params_into_sql($params = array()){
		$defaultParamFilters = self::get_default_param_filters();
		if(!count($defaultParamFilters)) {
			return ""; //no use for this if there are not parameters defined
		}
		$outputArray = array();
		foreach($defaultParamFilters as $field => $value){
			if(isset($params[$field])){
				//TODO: convert to $dbfield->prepValueForDB() when Boolean problem figured out
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
   * DEBUG
*******************************************************/


	function debug() {
		Debug::show(ShoppingCart::current_order());
	}



}

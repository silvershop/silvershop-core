<?php

/**
 * ShoppingCart is a session handler that stores
 * information about what products are in a user's
 * cart on the site.
 *
 * @package ecommerce
 */
class ShoppingCart extends Controller {

	protected static $order = null; // for temp caching
	static $cartid_session_name = 'shoppingcartid';
	static $URLSegment = 'shoppingcart';
	static $default_country = null;

	static $allowed_actions = array (
		'additem',
		'removeitem',
		'removeallitem',
		'removemodifier',
		'setcountry',
		'setquantityitem',
		'clear',
		'debug'
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
	protected static $paramfilters = array();

	function set_param_filters($array){
		self::$paramfilters = array_merge(self::$paramfilters,$array);
	}

	//Country functions

	static function country_setting_index() {
		return "ShoppingCartCountry";
	}

	static function set_country($country) {
		self::$default_country = $country;
		Session::set(self::country_setting_index(), $country);
	}

	static function get_country() {
		if($c = Session::get(self::country_setting_index())){
			return $c;
		}
		return self::$default_country;
	}

	static function remove_country() {
		Session::clear(self::country_setting_index());
	}

	//Controller links

	static function add_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/additem/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}

	static function remove_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/removeitem/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}

	static function remove_all_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/removeallitem/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}

	static function set_quantity_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/setquantityitem/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}

	static function remove_modifier_link($id, $variationid = null) {
		return self::$URLSegment.'/removemodifier/'.$id.self::variation_link($variationid);
	}


	static function set_country_link() {
		return self::$URLSegment.'/setcountry';
	}

	/** helper function for appending variation id */
	protected static function variation_link($variationid) {
		if (is_numeric($variationid)) {
			return "/$variationid";
		}
		return "";
	}

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
			return "?".implode("&",$array);
		}
		return "";
	}

	/**
	 * Finds or creates a current order.
	 * @todo split this into two functions: initcart, and currentcart...so that templates can return null for Cart
	 */
	public static function current_order() {
		if(self::$order) return self::$order; //we only want to hit the database once

		//find order by id saved to session (allows logging out and retaining cart contents)
		$cartid = Session::get(self::$cartid_session_name);
		//TODO: make clear cart on logout optional
		if ($cartid && $o = DataObject::get_one('Order', "\"Status\" = 'Cart' AND \"ID\" = $cartid")) {
			$order = $o;
		}else {
			$order = new Order();
			$order->SessionID = session_id();
			if(EcommerceRole::get_associate_to_current_order())
				$order->MemberID = Member::currentUserID(); // Set the Member relation to this order
			$order->write();
			Session::set(self::$cartid_session_name,$order->ID);
			//init modifiers the first time the order is created
				// (currently assumes modifiers won't change)
		}

		self::$order = $order; //temp caching
		$order->initModifiers(); //init /re-init modifiers
		$order->write(); // Write the order

		return $order;
	}

	/**
	 * Allow checking if order has started, because we don't always want to create a cart.
	 */
	public static function order_started(){
		$cartid = Session::get(self::$cartid_session_name);
		return (bool) (self::$order || ($cartid && DataObject::get_one('Order', "\"Status\" = 'Cart' AND \"ID\" = $cartid")));
	}

	// Static items management

	/**
	 * Either update or create OrderItem in ShoppingCart.
	 */
	static function add_new_item(OrderItem $item) {
		$item->write();
		self::current_order()->Attributes()->add($item);
	}

	/**
	 * Add a new OrderItem to session
	 */
	static function add_item($existingitem, $quantity = 1) {
		if ($existingitem) {
			$existingitem->Quantity += $quantity;
			$existingitem->write();
		}
	}

	/**
	 * Update quantity of an OrderItem in the session
	 */
	static function set_quantity_item($existingitem, $quantity) {
		if ($existingitem) {
			$existingitem->Quantity = $quantity;
			$existingitem->write();
		}
	}

	/**
	 * Reduce quantity of an orderItem, or completely remove
	 */
	static function remove_item($existingitem, $quantity = 1) {
		if ($existingitem) {
			if ($quantity >= $existingitem->Quantity) {
				$existingitem->delete();
				$existingitem->destroy();
			} else {
				$existingitem->Quantity -= $quantity;
				$existingitem->write();
			}
		}
	}

	static function remove_all_item($existingitem) {
		if($existingitem){
			$existingitem->delete();
			$existingitem->destroy();
		}
	}

	static function remove_all_items() {
		//TODO: make this ONLY remove items & not modifiers also?
		self::current_order()->Attributes()->removeAll();
	}

	/**
	 * Check if there are any items in the cart
	 */
	static function has_items() {
		return self::current_order()->Items() != null;
	}

	/**
	 * Return the items currently in the shopping cart.
	 * @return array
	 */
	static function get_items($filter = null) {
		return self::current_order()->Items($filter);
	}

	/**
	 * Get OrderItem according to product id, and coorresponding parameter filter.
	 */
	static function get_item_by_id($id, $variationid = null,$filter = null) {
		if(!$id) return null;

		$filter = self::get_param_filter($filter);
		if(is_numeric($variationid)){
			$filter .= ($filter && $filter != "") ? " AND " : "";
			$filter .= "\"ProductVariationID\" = $variationid";
		}
		$order = self::current_order();
		$fil = ($filter && $filter != "") ? " AND $filter" : "";

		$item = DataObject::get_one('OrderItem', "\"OrderID\" = $order->ID AND \"ProductID\" = $id". $fil);
		return $item;
	}

	/**
	 * Get item according to a filter.
	 */
	static function get_item($filter) {
		$order = self::current_order();
		if($filter) {
			$filterString = " AND ($filter)";
		}
		return  DataObject::get_one('OrderItem', "\"OrderID\" = $order->ID $filterString");
	}

	static function add_buyable($buyable,$quantity = 1){
		if(!$buyable || !$buyable->canPurchase()) return null;

		$item = self::find_or_make_order_item($buyable);
		if($item->ID){
			$item->Quantity += $quantity;
			$item->write();
		}else{
			$item->Quantity = $quantity;
			$item->write();
			self::add_new_item($item);
		}

		return $item;
	}

	static function get_buyable_by_id($productId, $variationId = null){
		$buyable = null;
		if (is_numeric($variationId) && is_numeric($productId)) {
			$buyable = DataObject::get_one('ProductVariation', sprintf("\"ID\" = %d AND \"ProductID\" = %d", (int) $variationId, (int) $productId));
		} elseif(is_numeric($productId)) {
			$buyable = Versioned::get_one_by_stage('Product','Live', '"Product_Live"."ID" = '.$productId); //only use live products
		}
		return $buyable;
	}

	static function find_or_make_order_item($buyable){
		$id = ($buyable instanceof ProductVariation) ? $buyable->ProductID : $buyable->ID;
		$varid = ($buyable instanceof ProductVariation) ? $buyable->ID : null;

		if($item = self::get_item_by_id($id,$varid)){
			return $item;
		}
		return self::create_order_item($buyable);
	}

	/**
	 * Creates a new order item based on url parameters
	 */
	static function create_order_item($buyable,$quantity = 1, $parameters = null){

		$orderitem = null;
		//create either a ProductVariation_OrderItem or a Product_OrderItem
		if ($buyable && $buyable instanceof ProductVariation) {
			if ($buyable && $buyable->canPurchase()) {
				$orderitem = new ProductVariation_OrderItem($buyable,$quantity);
			}
		} elseif($buyable &&  $buyable instanceof Product) {
			if ($buyable && $buyable->canPurchase()) {
				$orderitem = new Product_OrderItem($buyable,$quantity);
			}
		}

		//set extra parameters
		if($orderitem instanceof OrderItem && is_array($parameters)){
			foreach(self::$paramfilters as $param => $defaultvalue){
				$v = (isset($parameters[$param])) ? Convert::raw2sql($parameters[$param]) : $defaultvalue;
				$orderitem->$param = $v;
			}
		}

		return $orderitem;
	}

	/**
	 * Gets a SQL filter based on array of parameters.
	 *
	 * 	 Returns default filter if none provided,
	 *	 otherwise it updates default filter with passed parameters
	 */
	static function get_param_filter($params = array()){
		if(!self::$paramfilters) return ""; //no use for this if there are not parameters defined
		$outputarray = array();
		foreach($p = self::get_clean_param_array($params) as $field => $value){
			$outputarray[] = "\"".$field."\" = ".Convert::raw2sql($value);
		}
		return implode(" AND ",$outputarray);
	}

	static function get_clean_param_array($params = array()){
		$arr = array();
		foreach(self::$paramfilters as $field => $value){
			$arr[$field] = (isset($params[$field])) ? $params[$field] : $value;
		}
		return $arr;
	}


	// Modifiers management

	static function can_remove_modifier($modifierIndex) {
		$serializedModifierIndex = self::modifier_index($modifierIndex);
		if ($serializedModifier = Session::get($serializedModifierIndex)) {
			$unserializedModifier = unserialize($serializedModifier);
			return $unserializedModifier->CanRemove();
		}
		return false;
	}

	static function remove_modifier($modifierIndex) {
		$serializedModifierIndex = self::modifier_index($modifierIndex);
		Session::clear($serializedModifierIndex);
	}

	static function remove_all_modifiers() {
		self::current_order()->Attributes()->removeAll(); //TODO: make this ONLY remove modifiers
	}

	static function has_modifiers() {
		return self::get_modifiers() != null;
	}

	/**
	 * Get all the {@link OrderModifier} instances
	 * that are currently in use. To set them, use
	 * {@link Order::set_modifiers()}.
	 *
	 * @return array
	 */
	static function get_modifiers() {
		return self::current_order()->Modifiers();
	}

	static function uses_different_shipping_address(){
		return self::current_order()->UseShippingAddress;
	}

	static function set_uses_different_shipping_address($use = true){
		$order = self::current_order();
		$order->UseShippingAddress = $use;
		$order->write();
	}

	/**
	 * Sets appropriate status, and message and redirects or returns appropriately.
	 */
	 //TODO: it seems silly that this should be a static method just because self::clear is static
	static function return_data($status = "success",$message = null){

		if(Director::is_ajax()){
			return $status; //TODO: make this customisable between json, status message etc. Perhaps make this whole function custom.
			//return self::json_code(); //TODO: incorporate status / message
		}
		//TODO: set session / status in session (like Form sessionMesssage)
		Director::redirectBack();
	}

	/**
	 * Builds json object to be returned via ajax.
	 */
	static function json_code() {

		//$this->response->addHeader('Content-Type', 'application/json');
		$currentOrder = ShoppingCart::current_order();
		$js = array ();

		if ($items = $currentOrder->Items()) {
			foreach ($items as $item)
				$item->updateForAjax($js);
		}

		if ($modifiers = $currentOrder->Modifiers()) {
			foreach ($modifiers as $modifier)
				$modifier->updateForAjax($js);
		}
		$currentOrder->updateForAjax($js);
		return Convert::array2json($js);
	}

	//Controller Functinons / Actions

	/**
	 * Either increments the count or creates a new item.
	 */
	function additem($request) {
		if ($itemId = $request->param('ID') && $product = $this->buyableFromURL()) {

			if($item = ShoppingCart::get_item($this->urlFilter())) {
				ShoppingCart::add_item($item);
				return self::return_data("success","Extra item added"); //TODO: i18n
			}else {
				if($orderitem = $this->create_order_item($product,1,self::get_clean_param_array($this->getRequest()->getVars()))) {
					ShoppingCart::add_new_item($orderitem);
					return self::return_data("success","Item added"); //TODO: i18n
				}
			}
		}
		return self::return_data("failure","Item could not be added"); //TODO: i18n
	}

	function removeitem($request) {
		if ($item = ShoppingCart::get_item($this->urlFilter())) {
			ShoppingCart::remove_item($item);
			return self::return_data("success","Item removed");//TODO: i18n
		}
		return self::return_data("failure","Item could not be found in cart");//TODO: i18n
	}

	function removeallitem() {
		if ($item = ShoppingCart::get_item($this->urlFilter())) {
			ShoppingCart::remove_all_item($item);
			return self::return_data("success","Item fully removed");//TODO: i18n
		}
		return self::return_data("failure","Item could not be found in cart");//TODO: i18n
	}


	/**
	 * Ajax method to set an item quantity
	 */
	function setquantityitem($request) {
		$quantity = $request->getVar('quantity');
		$product = $this->buyableFromURL();
		if (is_numeric($quantity) && $product) {
			$item = ShoppingCart::get_item($this->urlFilter());
			if($quantity > 0){
				if(!$item){
					if($item = self::create_order_item($product,$quantity,self::get_clean_param_array($this->getRequest()->getVars()))){
						$item->Quantity = $quantity;
						self::add_new_item($item);
					}
				}
				else{
					ShoppingCart::set_quantity_item($item, $quantity);
				}
			}elseif($item){
				ShoppingCart::remove_all_item($item);
				return self::return_data("success","Item removed completely");//TODO: i18n
			}
			return self::return_data("success","Quantity set successfully");//TODO: i18n
		}
		return self::return_data("failure","Quantity provided is not numeric");//TODO: i18n
	}

	/**
	 * Removes specified modifier, if allowed
	 */
	function removemodifier() {
		$modifierId = $this->urlParams['ID'];
		if (ShoppingCart::can_remove_modifier($modifierId)){
			ShoppingCart::remove_modifier($modifierId);
			return self::return_data("success","Removed");//TODO: i18n
		}
		return self::return_data("failure","Could not be removed");//TODO: i18n
	}

	/**
	 * Clears the cart of all items and modifiers.
	 * It does this by disconnecting the current cart from the user session.
	 */
	static function clear($request = null) {
		if(self::order_started()){
			self::current_order()->SessionID = null;
			self::current_order()->write();
			self::remove_all_items();
		}
		self::$order = null;
		Session::clear(self::country_setting_index());

		//redirect back or send ajax only if called via http request.
		//This check allows this function to be called from elsewhere in the system.
		if($request instanceof SS_HTTPRequest){
			return self::return_data("success","Cart cleared");//TODO: i18n
		}
	}

	/**
	 * Retrieves the appropriate product, variation etc from url parameters.
	 */
	protected function buyableFromURL(){
		$request = $this->getRequest();
		$variationId = $request->param('OtherID');
		$productId = $request->param('ID');
		return self::get_buyable_by_id($productId,$variationId);
	}

	/**
	 * Gets a filter based on urlParameters
	 */
	function urlFilter(){
		$result = '';
		$request = $this->getRequest();
		$selection = array(
			"\"ProductID\" = ".$request->param('ID')
		);
		if(is_numeric($request->param('OtherID'))){
			$selection[] = "\"ProductVariationID\" = ".$request->param('OtherID');
		}

		$filter = self::get_param_filter($request->getVars());
		if( $filter ){
			$result = implode(" AND ",array_merge($selection,array($filter)));
		}
		else {
			$result = implode(" AND ",$selection);
		}
		return $result;
	}

	/**
	 * Displays order info and cart contents.
	 */
	function debug() {
		if(Director::isDev() || Permission::check("ADMIN"))
			Debug::show(ShoppingCart::current_order());
	}

	/**
	 *  Change country action
	 * */
	function setcountry($request) {
		$countryCode = $request->param('ID');
		if($countryCode && strlen($countryCode) < 4) {
			//to do: check if country exists
			ShoppingCart::set_country($countryCode);
			//return _t("ShoppingCart.COUNTRYUPDATED", "Country updated.");
			return self::json_code();
		}
		return _t("ShoppingCart.COUNTRYCOULDNOTBEUPDATED", "Country not be updated.");
	}

}
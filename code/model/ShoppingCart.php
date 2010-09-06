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

	static $allowed_actions = array (
		'additem',
		'removeitem',
		'removeallitem',
		'removemodifier',
		'setcountry',
		'setquantityitem',
		'clear',

		'debug' => 'ADMIN'
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

	function init() {
		parent::init();
		self::current_order();
		self::$order->initModifiers();
	}

	static $URLSegment = 'shoppingcart';
	
	//controller links
	static function add_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/additem/'.$id.self::variationLink($variationid).self::paramsToGetString($parameters);
	}

	static function remove_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/removeitem/'.$id.self::variationLink($variationid).self::paramsToGetString($parameters);
	}

	static function remove_all_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/removeallitem/'.$id.self::variationLink($variationid).self::paramsToGetString($parameters);
	}

	static function set_quantity_item_link($id, $variationid = null, $parameters = array()) {
		return self::$URLSegment.'/setquantityitem/'.$id.self::variationLink($variationid).self::paramsToGetString($parameters);
	}

	static function remove_modifier_link($id, $variationid = null) {
		return self::$URLSegment.'/removemodifier/'.$id.self::variationLink($variationid);
	}
	
	//TODO: this has no purpose currently
	static function set_country_link() {
		return self::$URLSegment.'/setcountry';
	}

	/** helper function for appending variation id */
	protected static function variationLink($variationid) {
		if (is_numeric($variationid)) {
			return "/$variationid";
		}
		return "";
	}
	
	/**
	 * Creates the appropriate string parameters for links from array
	 */
	protected static function paramsToGetString($array){
		if($array & count($array > 0)){
			array_walk($array , create_function('&$v,$k', '$v = $k."=".$v ;'));
			return "?".htmlentities(implode("&",$array), ENT_QUOTES); //TODO: urlescape values??
		}
		return "";
	}

	public static function current_order() {
		$order = self::$order;
		if (!$order) {
			//find order by session id
			//TODO: it might be better if the orderID is stored in the session,
			//becasue there could be some confusion retrieving from multiple orders with the same session ID
			if ($o = DataObject::get_one('Order', "Status = 'Cart' AND SessionID = '".session_id()."'")) {
				$order = $o;	
			}else {
				$order = new Order();
				$order->SessionID = session_id();
				$order->MemberID = Member::currentUserID(); // Set the Member relation to this order
				$order->write();				
			}
			self::$order = $order; //temp caching
		}
		$order->MemberID = Member::currentUserID(); // Set the Member relation to this order
		$order->write(); // Write the order
		return $order;
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
		//TODO: make this ONLY remove items & not modifiers also
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
	static function get_items($filter) {
		return self::current_order()->Items($filter);
	}

	static function get_item_by_id($id, $variationid = null,$filter = null) {
		$order = self::current_order();
		$defaultfilter = (self::defaultFilter() && self::defaultFilter() != "") ? " AND ". self::defaultFilter() : "";
		$fil = ($filter) ? " AND $filter" : $defaultfilter;
		return DataObject::get_one('OrderItem', "OrderID = $order->ID AND ProductID = $id". $fil);
	}
	
	static function get_item($filter) {
		$order = self::current_order();
		return  DataObject::get_one('OrderItem', "OrderID = $order->ID AND $filter");
	}

	// Modifiers management

	static function add_new_modifier(OrderModifier $modifier) {
		$modifier->write();
		self::current_order()->Attributes()->add($modifier);
	}

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

	// Clear function

	static function clear() {
		self::current_order()->SessionID = null;
		self::current_order()->write();
		self::$order = null;
	}

	// Database saving function
	static function save_current_order() {
		return Order::save_current_order();
	}
	
	static function json_code() {
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
	
	//Actions

	/**
	 * Either increments the count or creates a new item.
	 */
	function additem($request) {
		
		if ($itemId = $request->param('ID')) {
			if($item = ShoppingCart::get_item($this->getFilter())) {
				
				ShoppingCart::add_item($item);
			} else {
				if($orderitem = $this->getNewOrderItem())
					ShoppingCart::add_new_item($orderitem);
			}
		}
		if (!$this->isAjax())
			Director::redirectBack();
	}

	function removeitem($request) {
		if ($item = ShoppingCart::get_item($this->getFilter())) {
			ShoppingCart::remove_item($item);
		}
		if (!$this->isAjax())
			Director::redirectBack();
	}

	function removeallitem() {
		if ($item = ShoppingCart::get_item($this->getFilter())) {
			ShoppingCart::remove_all_item($item);
		}
		if (!$this->isAjax())
			Director::redirectBack();
	}

	/**
	 * Ajax method to set an item quantity
	 */
	function setquantityitem() {
		$quantity = $request->param('quantity');
		if ($quantity && $quantity > 0) {
			if ($item = ShoppingCart::get_item($this->getFilter()))
				ShoppingCart::set_quantity_item($item, $quantity);
		}
	}
	
	/**
	 * Create a filter for retrieving OrderItem, based on url & get params 
	 */
	protected function getFilter(){
		
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$request = $this->getRequest();
		
		$selection = array(
			'ProductID = '.$request->param('ID')
		);
		if($request->param('OtherID'))
			$selection[] = 'ProductVariationID = '.$request->param('OtherID');
		
		$paramarray = self::$paramfilters;
		
		foreach($paramarray as $param => $value){
			$v = ($request->getVar($param)) ? $request->getVar($param) : $value;
			$paramarray[$param] = "{$bt}$param{$bt} = ".Convert::raw2sql($v);  
		}
		
		$selection = array_merge($selection,$paramarray);
		return implode(" AND ",$selection);
	}
	
	/**
	 * Creates new order item based on url parameters
	 */
	protected function getNewOrderItem(){

		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$request = $this->getRequest();
		$orderitem = null;
		
		//create either a ProductVariation_OrderItem or a Product_OrderItem
		if (is_numeric($request->param('OtherID')) && $variationId = $request->param('OtherID')) {
			$variation = DataObject::get_one('ProductVariation', sprintf("{$bt}ID{$bt} = %d AND {$bt}ProductID{$bt} = %d", (int) $this->urlParams['OtherID'], (int) $this->urlParams['ID']));
			if ($variation && $variation->AllowPurchase()) {
				$orderitem = new ProductVariation_OrderItem($variation,1);
			}
		} elseif(is_numeric($request->param('ID')) && $itemId = $request->param('ID')) {
			$product = DataObject::get_by_id('Product', $itemId);
			if ($product && $product->AllowPurchase) {
				$orderitem = new Product_OrderItem($product,1);
			}
		}
		//set extra parameters
		if($orderitem instanceof OrderItem){
			foreach(self::$paramfilters as $param => $defaultvalue){
				$v = ($request->getVar($param)) ? Convert::raw2sql($request->getVar($param)) : $defaultvalue;
				$orderitem->$param = $v;
			}
		}
		return $orderitem;
	}
	
	static function defaultFilter(){
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$paramarray = self::$paramfilters;
		foreach($paramarray as $param => $value){
			$paramarray[$param] = "{$bt}$param{$bt} = ".Convert::raw2sql($value);  
		}
		return implode(" AND ",$paramarray);
	}
	
	function removemodifier() {
		$modifierId = $this->urlParams['ID'];
		if (ShoppingCart::can_remove_modifier($modifierId))
			ShoppingCart::remove_modifier($modifierId);
		if (!$this->isAjax())
			Director::redirectBack();
	}

	function debug() {
		Debug::show(ShoppingCart::current_order());
	}

}
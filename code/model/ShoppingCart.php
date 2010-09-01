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

	function init() {
		parent::init();
		self::current_order();
		self::$order->initModifiers();
	}

	static $URLSegment = 'shoppingcart';
	
	//controller links
	static function add_item_link($id, $variationid = null) {
		return self::$URLSegment.'/additem/'.$id.self::variationLink($variationid);
	}

	static function remove_item_link($id, $variationid = null) {
		return self::$URLSegment.'/removeitem/'.$id.self::variationLink($variationid);
	}

	static function remove_all_item_link($id, $variationid = null) {
		return self::$URLSegment.'/removeallitem/'.$id.self::variationLink($variationid);
	}

	static function set_quantity_item_link($id, $variationid = null) {
		return self::$URLSegment.'/setquantityitem/'.$id.self::variationLink($variationid);
	}

	static function remove_modifier_link($id, $variationid = null) {
		return self::$URLSegment.'/removemodifier/'.$id.self::variationLink($variationid);
	}

	static function set_country_link() {
		return self::$URLSegment.'/setcountry';
	}

	public static function current_order() {
		$order = self::$order;

		if (!$order) {
			//find order by session id	
			if ($o = DataObject::get_one('Order', "Status = 'Cart' AND SessionID = '".session_id()."'")) {
				$order = $o;
				//find order by member id
			}
			//FIXME: causes complications when carts are abandoned
			/*elseif (Member::currentUser() && $o = DataObject::get_one('Order', "Status = 'Cart' AND MemberID = ".Member::currentUser()->ID)) {
				$order = $o;
				//create new order
			}*/
			else {
				$order = new Order();
				$order->SessionID = session_id();
				$order->MemberID = Member::currentUserID(); // Set the Member relation to this order
				$order->write();				
			}
			self::$order = $order;
		}

		$order->MemberID = Member::currentUserID(); // Set the Member relation to this order
		
		$order->write(); // Write the order
		return $order;
	}

	//4) Items management

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
	static function add_item($itemIndex, $quantity = 1) {
		$attributes = self::current_order()->Attributes(); //TODO: change to $order->Items()
		if ($existingitem = $attributes->find('ProductID', $itemIndex)) {
			$existingitem->Quantity += $quantity;
			$existingitem->write();
		}
	}

	/**
	 * Update quantity of an OrderItem in the session
	 */
	static function set_quantity_item($itemIndex, $quantity) {
		$attributes = self::current_order()->Attributes(); //TODO: change to $order->Items()
		if ($existingitem = $attributes->find('ProductID', $itemIndex)) {
			$existingitem->Quantity = $quantity;
			$existingitem->write();
		}
	}

	/**
	 * Reduce quantity of an orderItem, or completely remove
	 */
	static function remove_item($itemIndex, $quantity = 1) {
		$attributes = self::current_order()->Attributes(); //TODO: change to $order->Items()
		if ($existingitem = $attributes->find('ProductID', $itemIndex)) {
			if ($quantity >= $existingitem->Quantity) {
				$existingitem->delete();
				$existingitem->destroy();
			} else {
				$existingitem->Quantity -= $quantity;
				$existingitem->write();
			}
		}
	}

	static function remove_all_item($itemIndex) {
		self::current_order()->Attributes()->remove($itemIndex);
	}

	static function remove_all_items() {
		self::current_order()->Attributes()->removeAll(); //TODO: make this ONLY remove items
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
	static function get_items() {
		return self::current_order()->Items();
	}

	static function get_item_by_id($id, $variationid = null) {
		$order = self::current_order();
		return DataObject::get_one('OrderItem', "OrderID = $order->ID AND ProductID = $id");
	}

	/**
	 * Serialise an OrderItem into the session.
	 */
	protected static function set_item($itemIndex, OrderItem $item) {
		$serializedItemIndex = self::item_index($itemIndex);
		Session::set($serializedItemIndex, serialize($item));
	}

	//5) Modifiers management


	static function add_new_modifier(OrderModifier $modifier) {
		$modifier->write();
		self::current_order()->Attributes()->add($modifier);
		
		//$modifiersTableIndex = self::modifiers_table_name();
		//Session::add_to_array($modifiersTableIndex, serialize($modifier));
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
		
		/*
		$modifiersTableIndex = self::modifiers_table_name();
		Session::clear($modifiersTableIndex);
		*/
	}

	static function has_modifiers() {
		return self::get_modifiers() != null;
		/*
		$modifiersTableIndex = self::modifiers_table_name();
		return Session::get($modifiersTableIndex) != null;
		*/
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


	//6) Clear function

	static function clear() {
		//self::remove_all_settings();
		//self::remove_all_items();
		//self::remove_all_modifiers();
		self::current_order()->SessionID = null;
		self::current_order()->write();
		self::$order = null;
	}

	//8) Database saving function
	static function save_current_order() {
		//TODO: change order status to 'Unpaid'
		return Order::save_current_order();
	}


	public static function json_code() {
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

	/** helper function for appending variation id */
	private static function variationLink($variationid) {
		if (is_numeric($variationid)) {
			return "/$variationid";
		}
		return "";
	}

	function additem() {

		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$itemId = $this->urlParams['ID'];
		$variationId = (is_numeric($this->urlParams['OtherID'])) ? $this->urlParams['OtherID'] : null;

		if ($itemId) {
			if (!ShoppingCart::get_item_by_id($itemId, $variationId)) { //if item doesn't exist in cart, then add_new_item
				if ($variationId) {
					$variation = DataObject::get_one('ProductVariation', sprintf("{$bt}ID{$bt} = %d AND {$bt}ProductID{$bt} = %d", (int) $this->urlParams['OtherID'], (int) $this->urlParams['ID']));
					if ($variation && $variation->AllowPurchase()) {

						ShoppingCart::add_new_item(new ProductVariation_OrderItem($variation,1));
					}
				} else {
					$product = DataObject::get_by_id('Product', $itemId);
					if ($product && $product->AllowPurchase) {
						ShoppingCart::add_new_item(new Product_OrderItem($product,1));
					}
				}
			} else {

				ShoppingCart::add_item($itemId.$this->variationParam());
			}
			if (!$this->isAjax())
				Director::redirectBack();
		}
	}

	function removeitem() {
		$itemId = $this->urlParams['ID'];
		if ($itemId) {
			ShoppingCart::remove_item($itemId.$this->variationParam());
			if (!$this->isAjax())
				Director::redirectBack();
		}
	}

	function removeallitem() {
		$itemId = $this->urlParams['ID'];
		if ($itemId) {
			ShoppingCart::remove_all_item($itemId.$this->variationParam());
			if (!$this->isAjax())
				Director::redirectBack();
		}
	}

	/**
	 * Ajax method to set an item quantity
	 */
	function setquantityitem() {
		$itemId = $this->urlParams['ID'];
		$quantity = $_REQUEST['quantity'];
		if ($itemId && is_numeric($quantity) && is_int($quantity +0)) {
			if ($quantity > 0) {
				ShoppingCart::set_quantity_item($itemId.$this->variationParam(), $quantity);
				return self::json_code();
			} else {
				user_error("Bad data to Product->setQuantity: quantity=$quantity", E_USER_WARNING);
			}
		} else {
			user_error("Bad data to Product->setQuantity: quantity=$quantity", E_USER_WARNING);
		}
	}

	/**
	 * Gets variation url param if there is one
	 */
	private function variationParam() {
		if (isset ($this->urlParams['OtherID'])) {
			return "_v".$this->urlParams['OtherID'];
		}
		return "";
	}

	function removemodifier() {
		$modifierId = $this->urlParams['ID'];
		if (ShoppingCart::can_remove_modifier($modifierId))
			ShoppingCart::remove_modifier($modifierId);
		if (!$this->isAjax())
			Director::redirectBack();
	}

	/**
	 * Set the country via url
	 */
	function setcountry() {
		$country = $this->urlParams['ID'];
		if (isset ($country)) {
			ShoppingCart::set_country($country);
			return self::json_code();
		}
	}

	function debug() {
		Debug::show(ShoppingCart::current_order());
	}

}
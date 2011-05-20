<?php
/**
 * ShoppingCart - provides a global way to interface with the cart (current order).
 *
 * This can be used in other code by calling $cart = ShoppingCart::singleton();
 *
 *
 * This version of shopping cart has been rewritten to:
 * - Seperate controller from the cart functions, abstracts out and encapsulates specific functionality.
 * - Reduce the excessive use of static variables.
 * - Clearly define an API for editing the cart, trying to keep the number of functions to a minimum.
 * - Allow easier testing of cart functionality.
 * - Message handling done in one place.
 * This is not taking a step backward, be cause the old ShoppingCart / Controller seperation had all static variables/functions on ShoppingCart
 *
 * @author: Jeremy Shipman, Nicolaas Francken
 * @package: ecommerce
 *

 * @todo country selection - this needs to be unified into one place
 * @todo handle rendering?
 *
 * @todo copying order - repeat orders
 *
 */
class ShoppingCart extends Object{

	/**
	 * used for setting/getting cart things from the session
	 *@var string
	 **/
	protected static $session_variable = "EcommerceShoppingCart";
		public static function get_session_variable(){return self::$session_variable;}
		public static function set_session_variable($s){self::$session_variable = $s;}

	/**
	 * indicates where carts are cleaned up all the time (the alternative is to setup a cron job).
	 *@var Boolean
	 **/
	protected static $cleanup_every_time = true;
		static function set_cleanup_every_time($bool = false){self::$cleanup_every_time = $bool;}


	/**
	 * Jeremy todo: explain how this works
	 *@var Array
	 **/
	protected static $default_param_filters = array();
		static function set_default_param_filters(array $paramarray){self::$default_param_filters = $paramarray;}

	/**
	 * Feedback message to user (e.g. cart updated, could not delete item, someone in standing behind you).
	 *@var Array
	 **/
	protected $messages = array();

	/**
	 * stores a reference to the current order object
	 *@var Object
	 **/
	protected $order = null;


	/**
	 * Allows access to the cart from anywhere in code.
	 * @return ShoppingCart Object
	 */
	protected static $singletoncart = null;
	public static function singleton(){
		if(!self::$singletoncart){
			self::$singletoncart = new ShoppingCart();
		}
		return self::$singletoncart;
	}

	/**
	 * Allows access to the current order from anywhere in the code..
	 * @return ShoppingCart Object
	 */
	public static function current_order() {
		return self::singleton()->currentOrder();
	}

	/**
	 * Adds any number of items to the cart.
	 * @param $buyable - the buyable (generally a product) being added to the cart
	 * @param $quantity - number of items add.
	 * @param $parameters - array of parameters to target a specific order item. eg: group=1, length=5
	 * @return the new item or null
	 */
	public function addBuyable($buyable,$quantity = 1, $parameters = array(),$overwriteqty = false){
		if($quantity <= 1 && $overwriteqty){ //special case remove
			$this->removeBuyable($buyable,'all',$parameters);
			return;
		}
		if($buyable->canPurchase() && $item = $this->findormakeitem($buyable,$parameters)){ //find existing order item or make one
			$quantity = (intval($quantity) >= 1 ) ? $quantity: 1; //ensuring sanity
			$item->Quantity = ($overwriteqty) ? $item->Quantity + $quantity : $quantity;
			$item->write();
			$this->currentOrder()->Attributes()->add($item); //save to current order
			//TODO: distinquish between incremented and set
			//TODO: use sprintf to allow product name etc to be included in message
			$this->addMessage(($quantity == 1)?_t("ShoppingCart.ITEMADDED", "Item added."):_t("ShoppingCart.ITEMSADDED", "Items added."),'good');
		}
		$this->addMessage(_t("ShoppingCart.ITEMCOULDNOTBEADDED", "Item could not be added."),'bad');
	}

	/**
	 * Removes any number of items from the cart.
	 * @return boolean - successfully removed
	 */
	public function removeBuyable($buyable,$quantity = 1, $parameters = array()){
		$item = $this->getExistingItem($buyable,$parameters);
		if(!$item){//check for existence of item
			$this->addMessage(_t("ShoppingCart.ITEMCOULDNOTBEFOUNDINCART", "Item could not found in cart."),'warning');
			return;
		}
		if($quantity <= 0){
			$this->addMessage(_t("ShoppingCart.CANTREMOVENONE", "It is not possible to reduce the quantity below one."),'warning');
			return;
		}
		$item->Quantity -= $quantity; //remove quantity
		if($item->Quantity <= 0 || $quantity == 'all'){ //remove all items from cart
			$this->currentOrder()->Attributes()->remove($item);
			$item->delete();
			$item->destroy();
			$this->addMessage(_t("ShoppingCart.ITEMCOMPLETELYREMOVED", "Item completely removed."),'good');
		}
		else{
			$item->write();
			$this->addMessage(_t("ShoppingCart.ITEMREMOVED", "Item removed."),'good');
		}
	}

	/**
	 * Clears the cart contents completely by removing the orderID from session, and thus creating a new cart on next request.
	 */
	public function clear(){
		Session::clear(self::$session_variable); //clear the orderid from session
		$this->order = null; //clear local variable
	}

	/**
	 * Removes a modifier from the cart
	 */
	public function removeModifier($modifier){
		$modifier = (is_numeric($modifier)) ? DataObject::get_by_id('OrderModifier',$modifier) : $modifier;
		if(!$modifier || !$modifier->CanBeRemoved()){
			$this->addMessage(_t("ShoppingCart.MODIFIERNOTREMOVED", "Could not be removed."),'bad');
			return;
		}
		$modifier->HasBeenRemoved = 1;
		$modifier->write();
		$this->addMessage(_t("ShoppingCart.MODIFIERREMOVED", "Removed."), 'good');
	}

	/**
	 * Sets an order as the current order.
	 *
	 */
	public function loadOrder($order){
		//TODO: how to handle existing order
		//TODO: permission check - does this belong to another member? ...or should permission be assumed already?
		if($this->order = (is_numeric($order)) ? DataObject::get_by_id('Order',$order) : $order){
			Session::set(self::$session_variable.".ID",$this->order->ID);
			$this->addMessage(_t("ShoppingCart.LOADEDEXISTING", "Order loaded."),'good');
		}
		else {
			$this->addMessage(_t("ShoppingCart.NOORDER", "No such order."),'bad');
		}
	}


	/**
	 * NOTE: tried to copy part to the Order Class - but that was not much of a go-er.
	 *@return DataObject(Order)
	 **/
	public function copyOrder($oldOrderID) {
		$oldOrder = Order::get_by_id_if_can_view($oldOrderID);
		if(!$oldOrder) {
			$this->addMessage(_t("ShoppingCart.NOORDER", "No such order."),'bad');
		}
		else {
			$newOrder = new Order();
			//for later use...
			$newOrder->write();
			$fieldList = array_keys(DB::fieldList("Order"));
			$this->loadOrder($newOrder);
			$items = DataObject::get("OrderItem", "\"OrderID\" = ".$oldOrder->ID);
			if($items) {
				foreach($items as $item) {
					$buyable = $item->Buyable($current = true);
					if($buyable->canPurchase()) {
						$this->addBuyable($buyable, $item->Quantity);
					}
				}
			}
			$newOrder->write();
			$this->addMessage(_t("ShoppingCart.ORDERCOPIED", "Order has been copied."),'good');
		}
	}

	/**
	 * Produces a debug of the shopping cart.
	 */
	public function debug(){
		Debug::show($this->currentOrder());
	}

	/*******************************************************
	* HELPER FUNCTIONS
	*******************************************************/

	/**
	 * Gets or creates the current order.
	 */
	protected function currentOrder(){
		if (!$this->order) {
			//TODO: try to retrieve incomplete member order
			$this->order = DataObject::get_by_id('Order',intval(Session::get(self::$session_variable.".ID"))); //find order by id saved to session (allows logging out and retaining cart contents)
			if(!$this->order){
				$this->order = new Order();
				$this->order->MemberID = Member::currentUserID();
				$this->order->write();
				Session::set(self::$session_variable.".ID",$this->order->ID);
			}
			$this->order->calculateModifiers();
		}
		return $this->order;
	}

	/**
	 * Helper function for making / retrieving order items.
	 * @param DataObject $buyable
	 * @param array $parameters
	 * @return OrderItem
	 */
	protected function findorMakeItem($buyable,$parameters = array()){
		//TODO: check for buyable existence & permission to do stuff
		if($item = $this->getExistingItem($buyable,$parameters)){
			return $item;
		}
		//otherwise create a new item
		$className = $buyable->classNameForOrderItem();
		$item = new $className();
		return $item;
	}

	/**
	 * Gets an existing order item based on buyable and passed parameters
	 * @param DataObject $buyable
	 * @param Array $parameters
	 * @return OrderItem or null
	 */
	protected function getExistingItem($buyable,$parameters = array()){
		$order = $this->currentOrder();
		$filterString = $this->parametersToSQL($parameters);
		return  DataObject::get_one('OrderItem', "\"OrderID\" = $order->ID $filterString");
	}

	/**
	 * Removes parameters that aren't in the default array, merges with default parameters, and converts raw2SQL.
	 * @param Array $parameters -  unclean array
	 * @return cleaned array
	 */
	protected function cleanParameters($params = array()){
		$newarray = array_merge(array(),self::$default_param_filters); //clone array
		if(!count($newarray)) {
			return array(); //no use for this if there are not parameters defined
		}
		foreach($newarray as $field => $value){
			if(isset($params[$field])){
				$newarray[$field] = Convert::raw2sql($params[$field]);
			}
		}
		return $newarray;
	}

	/**
	 * Converts parameter array to SQL query filter
	 */
	protected function parametersToSQL($parameters = array()){
		$defaultParamFilters = self::$default_param_filters;
		if(!count($defaultParamFilters)) {
			return ""; //no use for this if there are not parameters defined
		}
		$cleanedparams = $this->cleanParameters($parameters);
		$outputArray = array();
		foreach($cleanedparams as $field => $value){
			$outputarray[$field] = "\"".$field."\" = ".$value;
		}
		if(count($outputArray)) {
			return implode(" AND ",$outputArray);
		}
		return "";
	}

	/*******************************************************
	* UI MESSAGE HANDLING
	*******************************************************/

	/**
	 * Stores a message that can later be returned via ajax or to $form->sessionMessage();
	 * @param $message - the message, which could be a notification of successful action, or reason for failure
	 * @param $type - please use good, bad, warning
	 */
	protected function addMessage($message, $type = 'good'){
		$this->messages[] = array(
			'Message' => $message,
			'Type' => $type
		);
	}

	/**
	 * Retrieves all good, bad, and ugly messages that have been produced during the current request.
	 * @return array of messages
	 */
	function getMessages(){
		//get old messages
		$messages = unserialize(Session::get(ShoppingCart::get_session_variable()."Messages"));
		//clear old messages
		$messages = Session::set(ShoppingCart::get_session_variable()."Messages", "");
		//set to form????
		$this->messages = array_merge($message, $this->messages);
		return $this->messages;
	}

	/**
	 *Saves current messages in session for retrieving them later.
	 * @return array of messages
	 */
	function StoreMessagesInSession(){
		Session::set(ShoppingCart::get_session_variable()."Messages", serialize($this->messages));
	}

}

/**
 * ShoppingCart_Controller
 *
 * Handles the modification of a shopping cart via http requests.
 * Provides links for making these modifications.
 *
 * @author: Jeremy Shipman, Nicolaas Francken
 * @package: ecommerce
 *
 * @todo supply links for adding, removing, and clearing cart items
 * @todo link for removing modifier(s)
 */
class ShoppingCart_Controller extends Controller{


	/**
	 * URLSegment used for the Shopping Cart controller
	 *@var string
	 **/
	protected static $url_segment = 'shoppingcart';
		static function set_url_segment($s) {self::$url_segment = $s;}
		static function get_url_segment() {return self::$url_segment;}

	/**
	 * Class used to provide the Shopping Cart Response (e.g. JSON data)
	 *@var string
	 **/
	protected static $response_class = "CartResponse";
		static function set_response_class(string $s) {self::$response_class = $s;}
		static function get_response_class() {return self::$response_class;}

	protected $cart = null;

	function init() {
		parent::init();
		$this->cart = ShoppingCart::singleton();
	}

	public static $allowed_actions = array (
		'additem',
		'removeitem',
		'removeallitem',
		'removemodifier',
		'addmodifier',
		'setcountry',
		'setquantityitem',
		'clear',
		'numberofitemsincart',
		'showcart',
		'loadorder',
		'copyorder',
		'debug' => 'ADMIN'
	);

	/*******************************************************
	* CONTROLLER LINKS
	*******************************************************/

	public function Link($action = null) {
		return Controller::join_links(Director::baseURL(), $this->RelativeLink($action));
	}

	static function add_item_link($buyableID, $className = "Product", $parameters = array()) {
		return self::$url_segment.'/additem/'.$buyableID."/".$className."/".self::params_to_get_string($parameters);
	}

	static function remove_item_link($buyableID, $className = "Product", $parameters = array()) {
		return self::$url_segment.'/removeitem/'.$buyableID."/".$className."/".self::params_to_get_string($parameters);
	}

	static function remove_all_item_link($buyableID, $className = "Product", $parameters = array()) {
		return self::$url_segment.'/removeallitem/'.$buyableID."/".$className."/".self::params_to_get_string($parameters);
	}

	static function set_quantity_item_link($buyableID, $className = "Product", $parameters = array()) {
		return self::$url_segment.'/setquantityitem/'.$buyableID."/".$className."/".self::params_to_get_string($parameters);
	}

	static function remove_modifier_link($modifierID) {
		return self::$url_segment.'/removemodifier/'.$modifierID."/";
	}


	/**
	 * Helper function used by link functions
	 * Creates the appropriate url-encoded string parameters for links from array
	 *
	 * Produces string such as: MyParam%3D11%26OtherParam%3D1
	 *     ...which decodes to: MyParam=11&OtherParam=1
	 *
	 * you will need to decode the url with javascript before using it.
	 *
	 *@todo: check that comment description actually matches what it does
	 *@return String (URLSegment)
	 */
	protected static function params_to_get_string($array){
		if($array & count($array > 0)){
			array_walk($array , create_function('&$v,$k', '$v = $k."=".$v ;'));
			return "?".implode("&",$array);
		}
		return "";
	}

	/**
	 * Adds item to cart via controller action.
	 */
	public function additem(){
		$this->cart->addBuyable($this->buyable(),$this->quantity(),$this->parameters());
		$this->setMessageAndReturn();
	}

	/**
	 * Sets the exact passed quantity.
	 * Note: If no ?quantity=x is specified in URL, then quantity will be set to 1.
	 */
	public function setquantityitem(){
		$this->cart->addBuyable($this->buyable(),$this->quantity(),$this->parameters(),true);
		$this->setMessageAndReturn();
	}

	/**
	 * Removes item from cart via controller action.
	 */
	public function removeitem(){
		$this->cart->removeBuyable($this->buyable(),$this->quantity(),$this->parameters());
		$this->setMessageAndReturn();
	}

	/**
	 * Removes all of a specific item
	 */
	public function removeallitem(){
		$this->cart->removeBuyable($this->buyable(),'all',$this->parameters());
		$this->setMessageAndReturn();
	}

	/**
	 * Remoces a specified modifier from the cart;
	 */
	public function removemodifier(){
		$this->cart->removeModifier($request->param('ID'));
		$this->setMessageAndReturn();
	}


	/**
	 *@return String (message)
	 **/
	function setcountry($request) {
		$request = $this->getRequest();
		$countryCode = $request->param('ID');
		if($countryCode) {
			//set_country will check if the country code is actually allowed....
			ShoppingCart::set_country($countryCode);
		}
		$this->setMessageAndReturn();
	}

	function clear() {
		$this->cart->clear();
		Director::redirectBack();
		exit();
	}

	/**
	 * return number of items in cart
	 *@return integer
	 **/
	function numberofitemsincart() {
		$cart = $this->cart->CurrentOrder();
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
	 * Gets a buyable object based on URL actions
	 * @todo: should this be in ShoppingCart??
	 */
	protected function buyable(){
		$request = $this->getRequest();
		$className = $request->param('OtherID');
		$buyableID = $request->param('ID');
		if($className && $buyableID){
			$obj = DataObject::get_by_id($className,$buyableID); //TODO: possible unsafe class name being passed...do proper subclass check
			if($obj->ClassName == $className) {
				return $obj;
			}
		}
		return null;
	}

	/**
	 * Gets the requested quantity
	 */
	protected function quantity(){
		$qty = $this->getRequest()->getVar('quantity');
		if(is_numeric($qty)){
			return $qty;
		}
		return 1;
	}

	/**
	 * Gets the request parameters
	 * @param $getpost - choose between obtaining the chosen parameters from GET or POST
	 */
	protected function parameters($getpost = 'GET'){
		return ($getpost == 'GET') ? $request->getVars() : $_POST;
	}

	/**
	 * Packages up error/success messages from shopping cart and returns them to the client.
	 */
	protected function setMessageAndReturn(){

		//TODO: handle passing back multiple messages
		if(Director::is_ajax()){
			$responseClass = self::get_response_class();
			$obj = new $responseClass();
			return $obj->ReturnCartData($this->cart->getMessages());
		}
		else {
			//TODO: handle passing a message back to a form->sessionMessage
			$this->cart->StoreMessagesInSession();
			Director::redirectBack();
			return;
		}
	}

	/**
	 * Handy debugging action visit.
	 * Log in as an administrator and visit mysite/shoppingcart/debug
	 */
	function debug(){
		$this->cart->debug();
	}





}

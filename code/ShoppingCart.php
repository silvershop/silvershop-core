<?php
/**
 * Front-end manipulation of the current order using a singleton pattern.
 * 
 * Ensures that an order is only started when necessary, and only
 * that order is manipulated, until the order has been placed.
 * 
 * The basic requirement for starting an order is to add an item to the cart.
 * 
 * @package shop
 * @todo handle variations
 */
class ShoppingCart{
	
	private static $instance;
	private static $cartid_session_name = 'shoppingcartid';
	
	private $order;
	private $calculateonce = false;
	private $message, $type;
	
	/**
	 * Access for only allowing access to one (singleton) ShoppingCart.
	 */
	public static function singleton(){
		if(!self::$instance){
			self::$instance = new ShoppingCart();
		}
		return self::$instance;
	}
	
	/**
	* Singleton prevents constructing a ShoppingCart any other way.
	*/
	private function __construct(){}
	
	/**
	* Get the current order, or return null if it doesn't exist.
	*/
	public function current(){
		//hack to (re)calculate modifiers if template is being rendered
		//TODO: warning, this will fall down if this function is called from a user-called $something->renderWith('')
		if(!$this->calculateonce && SSViewer::topLevel() && $order = $this->order){
			$this->calculateonce = true;
			$order->calculate();
		}
		
		if($this->order) return $this->order;
		//find order by id saved to session (allows logging out and retaining cart contents)
		$sessionid = Session::get(self::$cartid_session_name);
		if ($sessionid && $order = DataObject::get_one('Order', "\"Status\" = 'Cart' AND \"ID\" = $sessionid")) {
			return $this->order = $order;
		}
		//TODO: get order by logged in member id?
		return false;
	}
	
	/**
	 * Helper that only allows orders to be started internally.
	 */
	protected function findOrMake(){
		if($this->current()) return $this->current();
		//otherwise start a new order
		$order = new Order();
		$order->SessionID = session_id();
		if(ShopMember::get_associate_to_current_order()){
			$order->MemberID = Member::currentUserID(); // Set the Member relation to this order
		}
		$order->write();
		Session::set(self::$cartid_session_name,$order->ID);
		$order->write();
		return $this->order = $order;
	}
	
	// Manipulations
	
	/**
	 * Adds an item to the cart
	 * 
	 * @param product $item - #TODO: should this be an id, or object?
	 * @param int $quantity
	 */
	public function add($product,$quantity = 1,$filter = array()){
		$order = $this->findOrMake();
		if(!$product){ 
			$this->error(_t("ShoppingCart.PRODUCTNOTFOUND","Product not found."));
			return false;
		}
		$item = $this->findOrMakeItem($product,$filter);
		if(!$item){	
			$this->error(_t("ShoppingCart.ITEMNOTCREATED","Item could not be created."));
			return false;
		}
		if(!$item->_brandnew){
			$item->Quantity += $quantity; //TODO: only increment if it is not a new item
		}else{
			$item->Quantity = $quantity;
		}
		$item->write();
		$this->message(_t("ShoppingCart.ITEMADD","Item has been added successfully."));
		return true;
	}
	
	/**
	 * Remove an item from the cart.
	 * @param $item
	 * @param int $quantity - number of items to remove, or leave null for all items (default)
	 */
	 public function remove($product,$quantity = null,$filter = array()){
		$order = $this->current();
		if(!$order){
			$this->error(_t("ShoppingCart.NOORDER","No current order."));
			return false;
		}
		$item = $this->get($product,$filter);
		if(!$item){
			return false;
		}
		$item->Quantity -= $quantity;
		if(!$quantity || $item->Quantity <= 0){ //if $quantity = 0, then remove all
			$item->delete();
			$item->destroy();
		}else{
			$item->write();
		}
		$this->message(_t("ShoppingCart.ITEMREMOVED","Item has been successfully removed."));
		return true;
	}
	
	/**
	 * Sets the quantity of an item in the cart.
	 * Will automatically add or remove item, if necessary.
	 * 
	 * @param $item
	 * @param int $quantity
	 */
	public function setQuantity($product,$quantity = 1,$filter = array()){
		if($quantity <= 0){
			return $this->remove($product,$quantity,$filter);
		}
		$order = $this->findOrMake();
		$item = $this->findOrMakeItem($product,$filter);
		if(!$item){
			return false;
		}
		$item->Quantity = $quantity;
		$item->write();
		$this->message(_t("ShoppingCart.QUANTITYSET","Quantity has been set."));
		return true;
	}
	
	/**
	 * Finds or makes an order item for a given product + filter.
	 * @param id or Product $product
	 * @param string $filter
	 */
	private function findOrMakeItem($product,$filter = array()){
		$order = $this->findOrMake();
		if(is_numeric($product)){
			$product = DataObject::get_by_id("Product", $product);
		}
		if(!$product || !$order){
			return false;
		}
		$item = $this->get($product,$filter);
		if(!$item){
			if(!$product->canPurchase()){
				$this->message(_t("ShoppingCart.CANNOTPURCHASE","This product cannot be purchased."));
				//TODO: get more specific message
				return false;
			}
			$item = $product->createItem(1,false,$filter);
			$item->OrderID = $order->ID;
			$item->write();
			$order->Attributes()->add($item);
			$item->_brandnew = true; //flag as being new
		}
		return $item;
	}
	
	/**
	 * Finds an existing order item.
	 * @param int or Product $product
	 * @param string $filter
	 * @return the item requested, or false
	 */
	public function get($product,$customfilter = array()){
		if(is_numeric($product)){
			$product = DataObject::get_by_id("Product", $product);
		}
		$order = $this->current();
		if(!$product || !$order) return false;
		//TODO: only use filter array, instead of 
		
		$filter = array(
			'OrderID' => $order->ID,
			'ProductID' => $product->ID
		);
		
		$itemclass = $product->stat('order_item');
		$singletonorderitem = singleton($itemclass);
		$required = $singletonorderitem->stat('required_fields');
		//TODO: $required = $itemclass::$required_fields; //php 5.3 isn't standard until SS3
		
		$required = array_merge(array('Order','Product'),$required);
		//TODO: allow passing exact id
		
		$query = new MatchObjectFilter($itemclass,array_merge($customfilter,$filter),$required);
		$item = DataObject::get_one($itemclass, $query->getFilter());
		if(!$item){
			$this->error(_t("ShoppingCart.ITEMNOTFOUND","Item not found."));
			return false;
		}
		return $item;
	}
	
	/**
	 * Empty / abandon the entire cart.
	 * @return bool - true if successful, fale if no cart found
	 */
	function clear() {
		$order = $this->current();
		if(!$order){
			$this->error(_t("ShoppingCart.NOCARTFOUND","No cart found."));
			return false;
		}
		//TODO: optionally delete the order from database
		$order->SessionID = null;
		$order->write();
		Session::clear(self::$cartid_session_name);
		$this->order = null;
		$this->message(_t("ShoppingCart.CLEARED","Cart was successfully cleared."));
		return true;
	}
	
	/**
	 * Store a new error.
	 */
	protected function error($message){
		$this->message($message,"bad");
	}
	
	/**
	 * Store a message to be fed back to user.
	 * @param string $message
	 * @param string $type - good, bad, warning
	 */
	protected function message($message,$type = "good"){
		$this->message = $message;
		$this->type = $type;
	}
	
	public function getMessage(){
		return $this->message;
	}
	
	public function getMessageType(){
		return $this->type;
	}
	
	//singleton protection
	
	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	
	public function __wakeup(){
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}
	
	//Deprecated, but needed in the mean time for things to work
	/**
	 * @deprecated use $cart = ShoppingCart::getInstance()->current();
	 */
	static function current_order(){
		return ShoppingCart::getInstance()->current();
	}
	
	/**
	 * @deprecated
	 */
	static function get_item_by_id(){}
	
	/**
	 * @deprecated
	 */
	static function order_started(){
		return (bool) ShoppingCart::getInstance()->current();
	}
	
	/**
	 * @deprecated this is checkout related
	 */
	static function uses_different_shipping_address(){
		if($order = ShoppingCart::getInstance()->current())
			return $order->UseShippingAddress;
	}
	
	/**
	 * @deprecated this is checkout related
	 */
	static function set_country_link(){}
	
	/**
	 * @deprecated this is checkout related
	 */
	static function get_country(){
		if($order = ShoppingCart::getInstance()->current())
			return ($order->ShippingCountry) ? $order->ShippingCountry : $order->Country;
	}
	
	/**
	 * @deprecated
	 */
	static function get_items($filter = null) {
		if($order = ShoppingCart::getInstance()->current())
			return $order->Items($filter);
	}
	
	/**
	 * @deprecated use ShoppingCart::singleton() instead.
	 */
	static function getInstance(){
		return self::singleton();
	}
	
}

/**
 * Manipulate the cart via urls.
 * 
 * @TODO handle filter stuff
 * @TODO introduce security token
 */
class ShoppingCart_Controller extends Controller{
	
	static $url_segment = "shoppingcart";
	private $cart;
	
	static $allowed_actions = array(
		'add',
		'additem',
		'remove',
		'removeitem',
		'removeall',
		'removeallitem',
		'setquantity',
		'setquantityitem',
		'clear'
	);
	
	static function add_item_link($id, $variationid = null, $parameters = array()) {
		return self::$url_segment.'/add/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}
	static function remove_item_link($id, $variationid = null, $parameters = array()) {
		return self::$url_segment.'/remove/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}
	static function remove_all_item_link($id, $variationid = null, $parameters = array()) {
		return self::$url_segment.'/removeall/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}
	static function set_quantity_item_link($id, $variationid = null, $parameters = array()) {
		return self::$url_segment.'/setquantity/'.$id.self::variation_link($variationid).self::params_to_get_string($parameters);
	}
	
	/** 
	 * helper function for appending variation id
	 */
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
	 */
	protected static function params_to_get_string($array){
		if($array & count($array > 0)){
			array_walk($array , create_function('&$v,$k', '$v = $k."=".$v ;'));
			return "?".implode("&",$array);
		}
		return "";
	}
	
	function init(){
		parent::init();
		$this->cart = ShoppingCart::getInstance();
	}
	
	function add($request){
		if($id = (int) $request->param('ID'))
			$this->cart->add($id);
		return $this->direct();
	}
	
	function remove($request){
		if($id = (int) $request->param('ID'))
			$this->cart->remove($id,$quantity = 1);
		return $this->direct();
	}
	
	function removeall($request){
		if($id = (int) $request->param('ID'))
			$this->cart->remove($id);
		return $this->direct();
	}
	
	function setquantity($request){
		$id = (int) $request->param('ID');
		$quantity = (int) $request->getVar('quantity');
		if($id)
			$this->cart->setQuantity($id,$quantity);
		return $this->direct();
	}
	
	function clear($request){
		$this->cart->clear();
		$this->direct();		
	}
	
	function direct($status = "success"){
		if(Director::is_ajax()){
			return $status;
		}
		Director::redirectBack();
	}	

	/**
	 * Handle index requests
	 */
	function index(){
		if($order = ShoppingCart::getInstance()->current()){
			Director::redirect($order->Link());
			return;
		}elseif($response = ErrorPage::response_for(404)) {
			return $response;
		}
		return $this->httpError(404, _t("ShoppingCart.NOCARTINITIALISED", "no cart initialised"));
	}
	
	/**
	* Displays order info and cart contents.
	*/
	function debug() {
		if(Director::isDev() || Permission::check("ADMIN")){
			Debug::show(ShoppingCart::getInstance()->current());
		}
	}
	
	//deprecated functions
	
	/**
	 * @deprecated
	 */
	function additem($request){
		$this->add($request);
	}
	/**
	 * @deprecated
	 */
	function removeitem($request){
		$this->remove($request);
	}
	/**
	 * @deprecated
	 */
	function removeallitem($request){
		$this->removeall($request);
	}
	/**
	 * @deprecated
	 */
	function setquantityitem($request){
		$this->setquantity($request);
	}
	
}
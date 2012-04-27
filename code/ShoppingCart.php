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
	public function add(Buyable $buyable,$quantity = 1,$filter = array()){
		$order = $this->findOrMake();
		if(!$buyable){ 
			$this->error(_t("ShoppingCart.PRODUCTNOTFOUND","Product not found."));
			return false;
		}
		$item = $this->findOrMakeItem($buyable,$filter);
		if(!$item){	
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
	 * 
	 * @param id or Buyable $buyable
	 * @param $item
	 * @param int $quantity - number of items to remove, or leave null for all items (default)
	 */
	 public function remove(Buyable $buyable,$quantity = null,$filter = array()){
		$order = $this->current();
		if(!$order){
			$this->error(_t("ShoppingCart.NOORDER","No current order."));
			return false;
		}
		$item = $this->get($buyable,$filter);
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
	 * @param id or Buyable $buyable
	 * @param $item
	 * @param int $quantity
	 */
	public function setQuantity(Buyable $buyable,$quantity = 1,$filter = array()){
		if($quantity <= 0){
			return $this->remove($buyable,$quantity,$filter);
		}
		$order = $this->findOrMake();
		$item = $this->findOrMakeItem($buyable,$filter);
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
	 * @param id or Buyable $buyable
	 * @param string $filter
	 */
	private function findOrMakeItem(Buyable $buyable,$filter = array()){
		$order = $this->findOrMake();
		if(!$buyable || !$order){
			return false;
		}
		$item = $this->get($buyable,$filter);
		if(!$item){
			if(!$buyable->canPurchase()){
				$this->message(sprintf(_t("ShoppingCart.CANNOTPURCHASE","This %s cannot be purchased."),strtolower($buyable->i18n_singular_name())),'bad');
				//TODO: get more specific message
				return false;
			}
			$item = $buyable->createItem(1,$filter);
			$item->OrderID = $order->ID;
			$item->write();
			$order->Attributes()->add($item);
			$item->_brandnew = true; //flag as being new
		}
		return $item;
	}
	
	/**
	 * Finds an existing order item.
	 * @param int or Buyable $buyable
	 * @param string $filter
	 * @return the item requested, or false
	 */
	public function get(Buyable $buyable, $customfilter = array()){
		$order = $this->current();
		if(!$buyable || !$order){
			return false;
		}
		$filter = array(
			'OrderID' => $order->ID
		);
		$itemclass = $buyable->stat('order_item');
		$singletonorderitem = singleton($itemclass);
		$relationship = $singletonorderitem->stat('buyable_relationship');
		$filter[$singletonorderitem->stat('buyable_relationship')."ID"] = $buyable->ID;
		$required = $singletonorderitem->stat('required_fields');
		//TODO: $required = $itemclass::$required_fields; //php 5.3 isn't standard until SS3
		$required = array_merge(array('Order',$singletonorderitem->stat('buyable_relationship')),$required);
		//TODO: allow passing exact id
		$query = new MatchObjectFilter($itemclass,array_merge($customfilter,$filter),$required);
		$filter = $query->getFilter();
		$item = DataObject::get_one($itemclass, $filter);
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
	protected $cart;
	
	static $allowed_actions = array(
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
	
	static function add_item_link(Buyable $buyable, $parameters = array()) {
		if($buyable->class != "Product")
			$parameters['buyable'] = $buyable->class;
		return self::$url_segment.'/add/'.$buyable->ID.self::params_to_get_string($parameters);
	}
	static function remove_item_link(Buyable $buyable, $parameters = array()) {
		if($buyable->class != "Product")
			$parameters['buyable'] = $buyable->class;
		return self::$url_segment.'/remove/'.$buyable->ID.self::params_to_get_string($parameters);
	}
	static function remove_all_item_link(Buyable $buyable, $parameters = array()) {
		if($buyable->class != "Product")
			$parameters['buyable'] = $buyable->class;
		return self::$url_segment.'/removeall/'.$buyable->ID.self::params_to_get_string($parameters);
	}
	static function set_quantity_item_link(Buyable $buyable, $parameters = array()) {
		if($buyable->class != "Product")
			$parameters['buyable'] = $buyable->class;
		return self::$url_segment.'/setquantity/'.$buyable->ID.self::params_to_get_string($parameters);
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
	
	protected function buyableFromRequest(){
		$request = $this->getRequest();
		if($id = (int) $request->param('ID')){
			$buyableclass = "Product";
			if($class = $request->getVar("buyable")){
				$buyableclass = Convert::raw2sql($class);
			}
			if($buyable = DataObject::get_by_id($buyableclass,$id)){

				return $buyable;
			}
		}
		return null;
	}
	
	function add($request){
		if($product = $this->buyableFromRequest())
			$this->cart->add($product);
		return $this->direct();
	}
	
	function remove($request){
		if($product = $this->buyableFromRequest())
			$this->cart->remove($product,$quantity = 1);
		return $this->direct();
	}
	
	function removeall($request){
		if($product = $this->buyableFromRequest())
			$this->cart->remove($product);
		return $this->direct();
	}
	
	function setquantity($request){
		$product = $this->buyableFromRequest();
		$quantity = (int) $request->getVar('quantity');
		if($product)
			$this->cart->setQuantity($product,$quantity);
		return $this->direct();
	}
	
	function clear($request){
		$this->cart->clear();
		return $this->direct();		
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
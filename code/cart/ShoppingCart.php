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
	 * Shortened alias for ShoppingCart::singleton()->current()
	 */
	public static function curr(){
		return ShoppingCart::singleton()->current();
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
		if ($sessionid && $order = Order::get()->filter(array("Status"=>"Cart","ID"=>$sessionid))->first()) {
			return $this->order = $order;
		}
		return false;
	}
	
	/**
	 * Set the current cart
	 */
	function setCurrent(Order $cart){
		if(!$cart->IsCart()){
			trigger_error("Passed Order object is not cart status", E_ERROR);
		}
		$this->order = $cart;
		Session::set(self::$cartid_session_name, $cart->ID);
		return $this;
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
		$order->extend('onStartOrder');
		Session::set(self::$cartid_session_name,$order->ID);
		return $this->order = $order;
	}
	
	// Manipulations
	
	/**
	 * Adds an item to the cart
	 * 
	 * @param Buyable $buyable
	 * @param number $quantity
	 * @param unknown $filter
	 * @return boolean
	 */
	public function add(Buyable $buyable,$quantity = 1,$filter = array()){
		$order = $this->findOrMake();
		$order->extend("beforeAdd",$buyable,$quantity,$filter);
		if(!$buyable){ 
			return $this->error(_t("ShoppingCart.PRODUCTNOTFOUND","Product not found."));
		}
		$item = $this->findOrMakeItem($buyable,$filter);
		if(!$item){	
			return false;
		}
		if(!$item->_brandnew){
			$item->Quantity += $quantity;
		}else{
			$item->Quantity = $quantity;
		}
		$item->write();
		$order->extend("afterAdd",$item,$buyable,$quantity,$filter);
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
		$order->extend("beforeRemove",$buyable,$quantity,$filter);
		if(!$order){
			return $this->error(_t("ShoppingCart.NOORDER","No current order."));
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
		$order->extend("afterRemove",$item,$buyable,$quantity,$filter);
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
		$order->extend("beforeSetQuantity",$buyable,$quantity,$filter);
		$item->Quantity = $quantity;
		$item->write();
		$order->extend("afterSetQuantity",$item,$buyable,$quantity,$filter);
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
			if(!$buyable->canPurchase(Member::currentUser())){
				return $this->error(sprintf(_t("ShoppingCart.CANNOTPURCHASE","This %s cannot be purchased."),strtolower($buyable->i18n_singular_name())));
				//TODO: produce a more specific message
			}
			$item = $buyable->createItem(1,$filter);
			$item->OrderID = $order->ID;
			$item->write();
			$order->Items()->add($item);
			$item->_brandnew = true; //flag as being new
		}
		return $item;
	}
	
	/**
	 * Finds an existing order item.
	 * @param Buyable $buyable
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
		
		$required = array_merge(
						array('Order',$singletonorderitem->stat('buyable_relationship')),
						$singletonorderitem->stat('required_fields')
					);
		$query = new MatchObjectFilter($itemclass,array_merge($customfilter,$filter),$required);
		$item = $itemclass::get()->where($query->getFilter())->first();
		if(!$item){
			return $this->error(_t("ShoppingCart.ITEMNOTFOUND","Item not found."));
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
			return $this->error(_t("ShoppingCart.NOCARTFOUND","No cart found."));
		}
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
		return false;
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
	
}

/**
 * Manipulate the cart via urls.
 */
class ShoppingCart_Controller extends Controller{
	
	static $url_segment = "shoppingcart";
	protected static $direct_to_cart_page = false;
	protected $cart;
	
	private static $url_handlers = array(
		'$Action/$Buyable/$ID' => 'handleAction',
	);
	
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
	
	static function set_direct_to_cart($direct = true){
		self::$direct_to_cart_page = $direct;
	}
	
	static function get_direct_to_cart(){
		return self::$direct_to_cart_page;
	}
	
	static function add_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("add", $buyable,$parameters);
	}
	static function remove_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("remove", $buyable,$parameters);
	}
	static function remove_all_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("removeall", $buyable,$parameters);
	}
	static function set_quantity_item_link(Buyable $buyable, $parameters = array()) {
		return self::build_url("setquantity", $buyable, $parameters);
	}
	
	/**
	 * Helper for creating a url
	 */
	protected static function build_url($action, $buyable, $params = array()){
		if(!$action || !$buyable){
			return false;
		}
		$params[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();		
		return self::$url_segment.'/'.$action.'/'.$buyable->class."/".$buyable->ID.self::params_to_get_string($params);
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
	
	static function direct($status = true){
		if(Director::is_ajax()){
			return $status;
		}
		if(self::$direct_to_cart_page && $cartlink = CartPage::find_link()){
			Controller::curr()->redirect($cartlink);
			return;
		}else{
			Controller::curr()->redirectBack();
			return;
		}
	}
	
	function init(){
		parent::init();
		$this->cart = ShoppingCart::singleton();
	}
	
	protected function buyableFromRequest(){
		$request = $this->getRequest();
		if(SecurityToken::is_enabled() && !SecurityToken::inst()->checkRequest($request)){
			return $this->httpError(400, _t("ShoppingCart.CSRF", "Invalid security token, possible CSRF attack."));
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
		// #146 - ensure only live products are returned
		if (Object::has_extension($buyableclass, 'Versioned')) {
			// There is a problem here because ProductVaration doesn't use a Live stage
			// and Versioned doesn't provide any clean way to determine what stages are
			// available for a given class.

			// The following does work in the case that a _Live table doesn't exist, but
			// to my mind it's not ideal because it will squash other errors. Leaving it
			// here as an alternate option, though. MG 2014-01-06
//			try {
//				$buyable = Versioned::get_by_stage($buyableclass, 'Live')->byID($id);
//			} catch (Exception $e) {
//				$buyable = DataObject::get($buyableclass)->byID($id);
//			}

			// The following requires an extra query, but is cleaner than the above
			$table = singleton($buyableclass)->baseTable() . '_Live';
			if (DB::getConn()->hasTable($table)) {
				$buyable = Versioned::get_by_stage($buyableclass, 'Live')->byID($id);
			} else {
				$buyable = DataObject::get($buyableclass)->byID($id);
			}
		} else {
			$buyable = DataObject::get($buyableclass)->byID($id);
		}

		if(!$buyable || !($buyable instanceof Buyable)){
			//TODO: store error message
			return null;
		}
		return $buyable;
	}
	
	function add($request){
		if($product = $this->buyableFromRequest()){
			$quantity = (int) $request->getVar('quantity');
			if(!$quantity) $quantity = 1;
			$this->cart->add($product,$quantity,$request->getVars());
		}
		return self::direct();
	}
	
	function remove($request){
		if($product = $this->buyableFromRequest())
			$this->cart->remove($product,$quantity = 1,$request->getVars());
		return self::direct();
	}
	
	function removeall($request){
		if($product = $this->buyableFromRequest())
			$this->cart->remove($product,null,$request->getVars());
		return self::direct();
	}
	
	function setquantity($request){
		$product = $this->buyableFromRequest();
		$quantity = (int) $request->getVar('quantity');
		if($product)
			$this->cart->setQuantity($product,$quantity,$request->getVars());
		return self::direct();
	}
	
	function clear($request){
		$this->cart->clear();
		return self::direct();		
	}

	/**
	 * Handle index requests
	 */
	function index(){
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
	function debug() {
		if(Director::isDev() || Permission::check("ADMIN")){
			//TODO: allow specifying a particular id to debug
			Requirements::css(SHOP_DIR."/css/cartdebug.css");
			$order = ShoppingCart::curr();
			$content = ($order) ? Debug::text($order) : "Cart has not been created yet. Add a product.";
			return array('Content' => $content);
		}
	}
	
}
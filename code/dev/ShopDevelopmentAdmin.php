<?php
/**
 * Provides a list of development tasks to perform.
 * @package shop
 * @subpackage dev
 */
class ShopDevelopmentAdmin extends Controller{

	static $url_handlers = array();

	static $allowed_actions = array(
		'index',
		'populatecart'
	);

	function init() {
		parent::init();

		// We allow access to this controller regardless of live-status or ADMIN permission only
		// if on CLI or with the database not ready. The latter makes it less errorprone to do an
		// initial schema build without requiring a default-admin login.
		// Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
		$isRunningTests = (class_exists('SapphireTest', false) && SapphireTest::is_running_test());
		$canAccess = (
			Director::isDev()
			|| !Security::database_is_ready()
			// We need to ensure that DevelopmentAdminTest can simulate permission failures when running
			// "dev/tests" from CLI.
			|| (Director::is_cli() && !$isRunningTests)
			|| Permission::check("ADMIN")
		);
		if(!$canAccess) {
			return Security::permissionFailure($this,
				"This page is secured and you need administrator rights to access it. " .
				"Enter your credentials below and we will send you right along.");
		}
		
		//render the debug view
		$renderer = Object::create('DebugView');
		$renderer->writeHeader();
		$renderer->writeInfo(_t("Shop.DEVTOOLSTITLE","Shop Development Tools"), Director::absoluteBaseURL());
		
	}
	
	/**
	 * Add 5 random Live products to cart, with random quantities between 1 and 10.
	 */
	function populatecart(){
		$cart = ShoppingCart::singleton();
		if($products = Versioned::get_by_stage("Product", "Live","","RAND()","",5)){
			foreach($products as $product){
				$cart->add($product,(int)rand(1, 10));
				//TODO: what about item attributes, variations, and custom buyables?
			}
		}
		$this->redirect($this->join_links(Director::baseURL(),'checkout'));
		return;
	}

	function ShopFolder(){
		return SHOP_DIR;
	}

	public function Link($action = null) {
		$action = ($action) ? $action : "";
		return Controller::join_links(Director::absoluteBaseURL(), 'dev/'.$this->ShopFolder().'/'.$action);
	}

}
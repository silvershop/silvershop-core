<?php

class EcommerceDatabaseAdmin extends Controller{
	
	static $url_handlers = array(
		//'' => 'browse',
	);
	
	static $allowed_actions = array(
		'deleteproducts',
		
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
	}
	
	
	function deleteproducts($request){
		$task = new DeleteEcommerceProductsTask();
		$task->run($request);	
	}
	
	
	private $tests = array(
			'ShoppingCartTest' => 'Shopping Cart',
			'OrderTest' => 'Order',
			'CheckoutPageTest' => 'Checkout Page',
			'EcommerceTest' => 'Ecommerce',
			//'OrderItemTest' => 'Order Item',
			'OrderModifierTest' => 'Order Modifier',
			'PaymentTest' => 'Payment',
			'ProductBulkLoaderTest' => 'Bulk Loader',
			'ProductOrderItemTest' => 'Product Order Item',
			'ProductTest' => 'Product',
	);
	
	function Tests(){
	    $dos = new DataObjectSet();
	    foreach($this->tests as $class => $name){
	    	$dos->push(new ArrayData(array(
	    		'Name' => $name,
	    		'Class' => $class
	    	)));	    	
	    }
	    return $dos; 
	}
	
	function AllTests(){
		return implode(',',array_keys($this->tests));		
	}
	
	public function Link($action = null) {
		$action = ($action) ? $action : "";
		return Controller::join_links(Director::absoluteBaseURL(), 'dev/ecommerce/'.$action);
	}
	
}

?>

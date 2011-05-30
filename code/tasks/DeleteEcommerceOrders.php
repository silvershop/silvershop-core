<?php

class DeleteEcommerceOrders extends BuildTask{
	
	protected $title = "Delete Orders";
	
	protected $description = "Deletes all orders, order items, and payments from the database.";
	
	function run($request){
		
		if($allorders = DataObject::get('Order')){
			foreach($allorders as $order){
				//TODO: delete member(s)?
				$order->delete();
				$order->destroy();
			}
		}
		
		$basetables = array(
			'Order',
			'OrderAttribute',	
			'OrderStatusLog',
			'Payment'
		);
		
		foreach($basetables as $table){
			if(!(ClassInfo::hasTable($table))) continue;
			
			foreach(ClassInfo::subclassesFor($table) as $key => $class){
				//TODO: empty all pivot(many_many) tables on this side of relationship	
				
				if(ClassInfo::hasTable($class)){
					DB::query("DELETE FROM \"$class\" WHERE 1;");
					echo "<p>Deleting all $class</p>";
				}
			}			
		}
		
	}
	
}

?>

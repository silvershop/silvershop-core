<?php
/**
 * Delete Ecommerce Orders
 * Deletes all orders, order items, and payments from the database.
 * @package shop
 * @subpackage tasks
 */
class DeleteOrdersTask extends BuildTask{

	protected $title = "Delete Orders";
	protected $description = "Deletes all orders, order items, and payments from the database.";

	function run($request = null){
		if($request && $request->getVar('type') == "sql"){
			$this->sqldelete();
		}else{
			$this->ormdelete();
		}
	}

	function ormdelete(){
		if($allorders = DataObject::get('Order')){
			foreach($allorders as $order){
				$order->delete();
				$order->destroy();
			}
		}
	}

	function sqldelete(){
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
				//TODO: only delete payments that have an orderid
		
				if(ClassInfo::hasTable($class)){
					echo "<p>Deleting all $class</p>";
					DB::query("DELETE FROM \"$class\" WHERE 1;");
				}
			}
		}
	}

}
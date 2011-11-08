<?php

/**
 * Re runs all calculation functions on all orders so that database is populated with pre-calculated values.
 * @author Jeremy
 */
class RecalculateAllOrdersTask extends BuildTask {

	protected $title = "Recalculate All Orders";

	protected $description = "Runs all price calculation functions on all orders.";

	function run($request){

		//TODO: include order total calculation, once that gets written

		if($orders = DataObject::get("Order")){
			echo "<br/>\nWriting all order items: ";
			foreach($orders as $order){
				if($items = $order->Items()){
					foreach($items as $item){
						if($item->Product()){
							echo $item->ID." ";
							$item->write(); //OrderItem->onBeforeWrite calls 'CalculateTotal'
						}
					}
				}
			}
		}
	}

}
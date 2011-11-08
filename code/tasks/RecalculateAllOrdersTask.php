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
		$items = DataObject::get("OrderItem");
		foreach($items as $item){
			if($item->Product()){
				$item->write(); //OrderItem->onBeforeWrite calls 'CalculateTotal'
			}
		}
	}

}
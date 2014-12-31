<?php

/**
 * Handles the calculation of order totals.
 *
 * Creates (if necessary) and calculates values for each modifier,
 * and subsequently the total of the order.
 * Caches to prevent recalculation, unless dirty.
 */
class OrderTotalCalculator{
	
	protected $order;

	function __construct(Order $order) {
		$this->order = $order;
	}

	function calculate() {
		$runningtotal = $this->order->SubTotal();
		$modifiertotal = 0;
		$sort = 1;
		$existingmodifiers = $this->order->Modifiers();
		$modifierclasses = Order::config()->modifiers;
		//check if modifiers are even in use
		if(!is_array($modifierclasses) || empty($modifierclasses)){
			return $runningtotal;
		}
		foreach($modifierclasses as $ClassName){
			if($modifier = $this->getModifier($ClassName)){
				$modifier->Sort = $sort;
				$runningtotal = $modifier->modify($runningtotal);
				if($modifier->isChanged()){
					$modifier->write();
				}
			}
			$sort++;
		}
		//clear old modifiers out
		if($existingmodifiers){
			foreach($existingmodifiers as $modifier){
				if(!in_array($modifier->ClassName, $modifierclasses)){
					$modifier->delete();
					$modifier->destroy();
				}
			}
		}
		//prevent negative sales from ever occurring
		if($runningtotal < 0){
			SS_Log::log(
				"Order (ID = {$this->order->ID}) was calculated to equal $runningtotal.\n
				Order totals should never be negative!\n
				The order total was set to $0",
				SS_Log::ERR
			);
			$runningtotal = 0;
		}

		return $runningtotal;
	}

	/**
	 * Retrieve a modifier of a given class for the order.
	 * Modifier will be retrieved from database if it already exists,
	 * or created if it is always required.
	 *
	 * @param string $className
	 * @param boolean $forcecreate - force the modifier to be created.
	 */
	public function getModifier($className, $forcecreate = false) {
		if(!ClassInfo::exists($className)){
			user_error("Modifier class \"$className\" does not exist.");
		}
		//search for existing
		$modifier = $className::get()
						->filter("OrderID", $this->order->ID)
						->first();
		if($modifier){
			//remove if no longer valid
			if(!$modifier->valid()){
				//TODO: need to provide feedback message - why modifier was removed
				$modifier->delete();
				$modifier->destroy();
				return null;
			}
			return $modifier;
		}
		$modifier = new $className();
		if($modifier->required() || $forcecreate){ //create any modifiers that are required for every order
			$modifier->OrderID = $this->order->ID;
			$modifier->write();
			$this->order->Modifiers()->add($modifier);

			return $modifier;
		}

		return null;
	}

}

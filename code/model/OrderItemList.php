<?php

/**
 * Additional template functions for Item lists.
 */
class OrderItemList extends Extension{
	
	function Quantity(){
		$quantity = 0;
		if($this->owner->exists()){
			foreach($this->owner as $item){
				$quantity += $item->Quantity;
			}
		}
		return $quantity;
	}
	
	function Plural(){
		return $this->Quantity() > 1;
	}
	
}
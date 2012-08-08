<?php

/**
 * Additional template functions for Item lists.
 */
class OrderItemList extends Extension{
	
	function Quantity(){
		return $this->owner->Sum('Quantity');
	}
	
	function Plural(){
		return $this->Quantity() > 1;
	}
	
	function Sum($field){
		$total = 0;
		if($this->owner->exists()){
			foreach($this->owner as $item){
				$total += $item->$field;
			}
		}
		return $total;
	}
	
}
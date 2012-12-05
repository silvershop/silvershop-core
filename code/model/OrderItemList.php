<?php
/**
 * Additional functions for Item lists.
 */
class OrderItemList extends Extension{
	
	function Quantity(){
		$total = 0;
		if($this->owner->exists()){
			foreach($this->owner as $item){
				$total += $item->Quantity;
			}
		}
		return $total;
	}
	
	function Plural(){
		return $this->Quantity() > 1;
	}
	
	/**
	 * Sums up all of desired field for items, and multiply by quantity.
	 * Optionally sum product field instead.
	 * @param $field - field to sum
	 * @param boolean $onproduct - sum from product or not
	 * @return number total
	 */
	function Sum($field, $onproduct = false){
		$total = 0;
		if($this->owner->exists()){
			foreach($this->owner as $item){
				if(!$onproduct){
					$total += $item->$field * $item->Quantity;
				}elseif($product = $item->Product()){
					$total += $product->$field * $item->Quantity;
				}
			}
		}
		return $total;
	}
	
}
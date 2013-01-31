<?php
/**
 * Additional functions for Item lists.
 */
class OrderItemList extends Extension{
	
	function Quantity(){
		return $this->owner->Sum('Quantity');
	}
	
	function Plural(){
		return $this->Quantity() > 1;
	}
	
	/**
	 * Sums up all of desired field for items. Optionally sum product field instead.
	 * @param $field - field to sum
	 * @param boolean $onproduct - sum from product or not
	 * @return number total
	 */
	
	function orderItemsSum($field, $onproduct = false){
		$total = 0;
		if($this->owner->exists()){
			foreach($this->owner as $item){
				if(!$onproduct){
					$total += $item->$field;
				}elseif($product = $item->Product()){
					$total += $product->$field;
				}
			}
		}
		return $total;
	}
	
}
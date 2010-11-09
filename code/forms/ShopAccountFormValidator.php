<?php

class ShopAccountFormValidator extends RequiredFields{

	/**
	 * Ensures member unique id stays unique.
	 */
	function php($data){
		
		$valid = parent::php($data);

		$field = Member::get_unique_identifier_field();
		if(isset($data[$field])){
			
			$uid = $data[Member::get_unique_identifier_field()];
			$currentmember = Member::currentUser();
			
			//can't be taken
			if(DataObject::get_one('Member',"$field = '$uid' AND ID != ".$currentmember->ID)){
				
				$this->validationError(
						$field,
						"\"$uid\" is already taken by another member. Try another.",
						"required"
				);
				$valid = false;
			}
		}
		
		return $valid;
	}
	
}
<?php


/**
 * Adds the ability to use the member's address book for choosing addresses
 *
 * @todo WIP / untested
 */
abstract class AddressBookCheckoutComponent extends AddressCheckoutComponent{

	public function getFormFields(Order $order){
		$fields = parent::getFormFields($order);
		if($existingaddressfields = $this->getExistingAddressFields()){
			$existingaddressfields->merge($fields);

			return $existingaddressfields;
		}

		return $fields;
	}

	/**
	 * Allow choosing from an existing address
	 * @return FieldList|null fields for
	 */
	public function getExistingAddressFields(){
		$member = Member::currentUser();
		if($member && $member->AddressBook()->exists()){
			$addressoptions = $member->AddressBook()->sort('Created','DESC')->map('ID','toString')->toArray();
			$addressoptions['newaddress'] = 'Create new address';
			$fieldtype = count($addressoptions) > 3 ? 'DropdownField' : 'OptionsetField';
			return new FieldList(
				$fieldtype::create($this->addresstype."AddressID","Existing Address",
					$addressoptions,
					$member->{"Default".$this->addresstype."AddressID"}
				)
			);
		}

		return null;
	}

	public function validateData(Order $order, array $data){
		//TODO: if existing address selected, check that it exists in $member->AddressBook
	}


}

class ShippingAddressBookCheckoutComponent extends AddressBookCheckoutComponent{

	protected $addresstype = "Shipping";

}

class BillingAddressBookCheckoutComponent extends AddressBookCheckoutComponent{

	protected $addresstype = "Billing";

}

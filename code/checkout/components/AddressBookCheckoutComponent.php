<?php

/**
 * Adds the ability to use the member's address book for choosing addresses
 *
 */
abstract class AddressBookCheckoutComponent extends AddressCheckoutComponent{

	private static $composite_field_tag = 'div';

	protected $addtoaddressbook = true;

	public function getFormFields(Order $order) {
		$fields = parent::getFormFields($order);

		if($existingaddressfields = $this->getExistingAddressFields()){
			Requirements::javascript('shop/javascript/CheckoutPage.js');
			// add the fields for a new address after the dropdown field
			$existingaddressfields->merge($fields);
			// group under a composite field (invisible by default) so we
			// easily know which fields to show/hide
			$label = _t(
				"AddressBookCheckkoutComponent.{$this->addresstype}Address",
				"{$this->addresstype} Address"
			);

			return new FieldList(
				CompositeField::create($existingaddressfields)
					->addExtraClass('hasExistingValues')
					->setLegend($label)
					->setTag(Config::inst()->get('AddressBookCheckoutComponent', 'composite_field_tag'))
			);
		}

		return $fields;
	}

	/**
	 * Allow choosing from an existing address
	 * @return FieldList|null fields for
	 */
	public function getExistingAddressFields() {
		$member = Member::currentUser();
		if($member && $member->AddressBook()->exists()){
			$addressoptions = $member->AddressBook()->sort('Created', 'DESC')->map('ID', 'toString')->toArray();
			$addressoptions['newaddress'] = _t("AddressBookCheckoutComponent.CREATENEWADDRESS", "Create new address");
			$fieldtype = count($addressoptions) > 3 ? 'DropdownField' : 'OptionsetField';
			$label = _t("AddressBookCheckoutComponent.Existing{$this->addresstype}Address", "Existing {$this->addresstype} Address");

			return new FieldList(
				$fieldtype::create($this->addresstype."AddressID", $label,
					$addressoptions,
					$member->{"Default".$this->addresstype."AddressID"}
				)->addExtraClass('existingValues')
			);
		}

		return null;
	}

	/**
	 * We don't know at the front end which fields are required so we defer to validateData
	 * @param Order $order
	 * @return array
	 */
	public function getRequiredFields(Order $order) {
		return array();
	}

	/**
	 * @param Order $order
	 * @param array $data
	 * @throws ValidationException
	 */
	public function validateData(Order $order, array $data) {
		$result = new ValidationResult();
		$existingID = !empty($data[$this->addresstype."AddressID"]) ? (int)$data[$this->addresstype."AddressID"] : 0;

		if ($existingID) {
			// If existing address selected, check that it exists in $member->AddressBook
			if (!Member::currentUserID() || !Member::currentUser()->AddressBook()->byID($existingID)) {
				$result->error("Invalid address supplied", $this->addresstype."AddressID");
				throw new ValidationException($result);
			}
		} else {
			// Otherwise, require the normal address fields
			$required = parent::getRequiredFields($order);
			foreach ($required as $fieldName) {
				if (empty($data[$fieldName])) {
					$errorMessage = _t(
						'Form.FIELDISREQUIRED',
						'{name} is required',
						array('name' => $fieldName)
					);

					$result->error($errorMessage, $fieldName);
					throw new ValidationException($result);
				}
			}
		}

	}

	/**
	 * Create a new address if the existing address has changed, or is not yet
	 * created.
	 *
	 * @param Order $order order to get addresses from
	 * @param array $data  data to set
	 */
	public function setData(Order $order, array $data) {
		$existingID = !empty($data[$this->addresstype."AddressID"]) ? (int)$data[$this->addresstype."AddressID"] : 0;
		if ($existingID > 0) {
			$order->{$this->addresstype."AddressID"} = $existingID;
			$order->write();
			$order->extend('onSet'.$this->addresstype.'Address', $address);
		} else {
			parent::setData($order, $data);
		}
	}
}

class ShippingAddressBookCheckoutComponent extends AddressBookCheckoutComponent{

	protected $addresstype = "Shipping";

}

class BillingAddressBookCheckoutComponent extends AddressBookCheckoutComponent{

	protected $addresstype = "Billing";

}

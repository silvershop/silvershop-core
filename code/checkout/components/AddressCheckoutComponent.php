<?php

abstract class AddressCheckoutComponent extends CheckoutComponent{

	protected $formfielddescriptions = true;

	protected $addresstype;

	public function getFormFields(Order $order) {
		return $this->getAddress($order)->getFrontEndFields(array(
			'addfielddescriptions' => $this->formfielddescriptions
		));
	}

	public function getRequiredFields(Order $order) {
		return $this->getAddress($order)->getRequiredFields();
	}

	public function validateData(Order $order, array $data) {

	}

	public function getData(Order $order) {
		$data = $this->getAddress($order)->toMap();
		unset($data['ID']);
		unset($data['ClassName']);
		unset($data['RecordClassName']);
		//merge data from multiple sources
		$member = Member::currentUser();

		$data = array_merge(
			ShopUserInfo::singleton()->getLocation(),
			$member ? $member->{"Default".$this->addresstype."Address"}()->toMap() : array(),
			$data
		);

		return $data;
	}

	/**
	 * Create a new address if the existing address has changed, or is not yet 
	 * created.
	 *
	 * @param Order $order order to get addresses from
	 * @param array $data  data to set
	 */
	public function setData(Order $order, array $data) {
		$address = $this->getAddress($order);
		$address->update($data);

		if(!$address->isInDB()) {
			$address->write();
		} elseif($address->isChanged()){
			$address = $address->duplicate();
		}

		$order->{$this->addresstype."AddressID"} = $address->ID;
		if(!$order->BillingAddressID){
			$order->BillingAddressID = $address->ID;
		}
		$order->write();

		if($this->addresstype === "Shipping") {
			ShopUserInfo::singleton()->setAddress($address);
			Zone::cache_zone_ids($address);
		}

		if($member = Member::currentUser()){
			$default = $member->{"Default".$this->addresstype."Address"}();

			if(!$default->exists()) {
				$member->{"Default".$this->addresstype."AddressID"} = $address->ID;
				$member->write();
			}
		}

		$order->extend('onSet'.$this->addresstype.'Address', $address);
	}

	/**
	 * Enable adding form field descriptions
	 */
	public function setShowFormFieldDescriptions($show = true) {
		$this->formfielddescriptions = $show;
	}

	public function getAddress(Order $order) {
		return $order->{$this->addresstype."Address"}();
	}

}

class ShippingAddressCheckoutComponent extends AddressCheckoutComponent {

	protected $addresstype = "Shipping";

}

class BillingAddressCheckoutComponent extends AddressCheckoutComponent {

	protected $addresstype = "Billing";

}

<?php


/**
 * @description: each order has an address: a Shipping and a Billing address
 * This is a base-class for both.
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: member
 *
 **/

class OrderAddress extends DataObject {

	/**
	 *Do the goods need to he shipped and if so,
	 * do we allow these goods to be shipped to a different address than the billing address?
	 *
	 *@var Boolean
	 **/

	protected static $use_separate_shipping_address = false;
		static function set_use_separate_shipping_address($b){self::$use_separate_shipping_address = $b;}
		static function get_use_separate_shipping_address(){return self::$use_separate_shipping_address;}

	//e.g. http://www.nzpost.co.nz/Cultures/en-NZ/OnlineTools/PostCodeFinder
	static function get_postal_code_url() {$sc = DataObject::get_one('SiteConfig'); if($sc) {return $sc->PostalCodeURL;}  }

	static function get_postal_code_label() {$sc = DataObject::get_one('SiteConfig'); if($sc) {return $sc->PostalCodeLabel;}  }

	protected static $include_state = false;
		static function set_include_state($b) {self::$include_state = $b;}
		static function get_include_state() {return self::$include_state;}

	public static $singular_name = "Order Address";
		function i18n_singular_name() { return _t("OrderAddress.ORDERADDRESS", "Order Address");}

	public static $plural_name = "Order Addresses";
		function i18n_plural_name() { return _t("OrderAddress.ORDERADDRESSES", "Order Addresses");}

	/**
	 *
	 *@return FieldSet
	 **/
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField("OrderID", $fields->dataFieldByName("OrderID")->performReadonlyTransformation());
		return $fields;
	}

	/**
	 *
	 *@return FieldSet
	 **/
	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	/**
	 *@return Fieldset
	 **/
	function getEcommerceFields() {
		$fields = new FieldSet();
		return $fields;
	}

	/**
	 * Copies the last address used by the member.
	 *@return DataObject (OrderAddress / ShippingAddress / BillingAddfress)
	 **/
	public function CopyLastAddressFromMember($member = null, $write = true) {
		if(!$member) {
			//cant use "Current Member" here, because the order might be created by the Shop Admin...
			$member = $this->getMemberFromOrder();
		}
		if($member) {
			$oldAddress = $this->LastAddressFromMember($member);
			if($oldAddress) {
				return $this->copyLastAddress($oldAddress, $write);
			}
		}
		return $this;
	}

	/**
	 * Finds the last address used by this member
	 *@return DataObject (OrderAddress / ShippingAddress / BillingAddfress)
	 **/
	public function LastAddressFromMember($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if($member) {
			$orders = DataObject::get("Order", "\"MemberID\" = '".$member->ID."'", "\"Created\" DESC", $join = null, $limit = "1");
			if($orders) {
				$order = $orders->First();
				if($order) {
					$address = DataObject::get_one($this->ClassName, "\"OrderID\" = '".$order->ID."'");
					return $address;
				}
			}
		}
	}

	/**
	 * copies old address to current record
	 * @param $oldAddress OrderAddress
	 * @return OrderAddress
	 **/
	protected function copyOldAddress($oldAddress, $write = true) {
		if($oldAddress) {
			if($this instanceOf BillingAddress) {
				$prefix = "";
			}
			elseif($this instanceOf ShippingAddress) {
				$prefix = "Shipping";
			}
			$fieldNameArray = $this->getFieldNameArray($prefix);
			foreach($fieldNameArray as $field) {
				if(!$this->$field) $this->$field = $oldAddress->$field;
			}
			if($write) {
				$this->write();
			}
		}
		return $this;
	}

	protected function getMemberFromOrder() {
		if($this->OrderID) {
			if($order = $this->Order()) {
				if($order->MemberID) {
					return DataObject::get_by_id("Member", $order->MemberID);
				}
			}
		}
	}

	/**
	*
	* @return OrderAddress with both Shipping and Billing Fields
	**/
	public function makeAddressShippingAndBilling() {
		if($this instanceOf BillingAddress) {
			$prefix1 = "Shipping";
			$prefix2 = "";
		}
		elseif($this instanceOf ShippingAddress) {
			$prefix1 = "";
			$prefix2 = "Shipping";
		}
		$fieldNameArray1 = $this->getFieldNameArray($prefix1);
		$fieldNameArray2 = $this->getFieldNameArray($prefix2);
		foreach($fieldNameArray1 as $key => $field1) {
			$field2 = $fieldNameArray2[$key];
			$this->$field1 = $this->$field2;
		}
		return $this;
	}


	public function populateDefaults() {
		parent::populateDefaults();
	}

	public function SetCountry($countryCode) {
		$this->Country = $countryCode;
		$this->ShippingCountry = $countryCode;
		$this->write();
	}

	protected function getFieldNameArray($prefix = '') {
		$fieldNameArray = array(
			"FirstName",
			"Surname",
			"Address",
			"Address2",
			"City",
			"PostalCode",
			"State",
			"Country",
			"Phone"
		);
		if($prefix) {
			foreach($fieldNameArray as $key => $value) {
				$fieldNameArray[$key] = $prefix.$value;
			}
		}
		return $fieldNameArray;
	}
}


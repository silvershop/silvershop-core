<?php
/**
 * Calculates the shipping cost of an order, by taking the products
 * and calculating the shipping weight, based on an array set in _config
 *
 * ASSUMPTION: The total order weight can be at maximum the last item
 * in the $shippingCosts array.
 *
 * @package ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class WeightShippingModifier extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	static $db =array(
		"TotalWeight" => "Double",
		"Country" => "Varchar(3)"
	);

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)
// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)

	protected static $a, $b;

// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

	public function runUpdate() {
		$this->checkField("TotalWeight");
		$this->checkField("Country");
		parent::runUpdate();
	}

// ######################################## *** form functions (e. g. showform and getform)
// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ...  ... USES DB VALUES
// ######################################## ***  inner calculations....  ... USES CALCULATED VALUES


	/**
	 * Retrieve the cost from Geoip::$default_country_code shipping
	 */
	protected function nationalCost(){
		// if a product can't have a weight, don't charge/display it
		if($this->LiveTotalWeight() <= 0) {
			return '0.00';
		}
		// return the pricing appropriate for the weight
		$shippingCosts = self::$a[Geoip::$default_country_code];
		return $this->getCostFromWeightList($shippingCosts);
	}

	/**
	 * Retrieve the cost from overseas shipping
	 */
	protected function internationalCost(){
		// if a product can't have a weight. Don't charge/display it
		if($this->LiveTotalWeight() <= 0) {
			return '0.00';
		}
		// return the pricing appropriate for the weight
		$shippingCosts = self::$a[$this->Country];

		// if there isn't any country code specifically in the array, use a zone instead
		if(! $shippingCosts) {
			$zone = self::$b[$this->Country];
			$shippingCosts = self::$a[$zone];
		}
		return $this->getCostFromWeightList($shippingCosts);
	}

	/**
	 * Get the cost from a list of max-weight => cost pairs
	 */
	protected function getCostFromWeightList($shippingCosts) {
		if($shippingCosts) {
			foreach($shippingCosts as $weight => $cost) {
				if($this->LiveTotalWeight() < $weight) {
					return $cost;
				}
			}
			return array_pop($shippingCosts);
		}
	}


// ######################################## *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES

	protected function LiveTotalWeight() {
		$totalWeight = 0;
		$order = $this->Order();
		$orderItems = $order->Items();
		// Calculate the total weight of the order
		if($orderItems) {
			foreach($orderItems as $orderItem) $totalWeight += $orderItem->Weight * $orderItem->Quantity;
		}
		return $totalWeight;
	}

	protected function LiveCountry() {
		return ShoppingCart::get_country();
	}

	/**
	 * Calculates the extra charges from the order based on the weight attribute of a product
 	 * ASSUMPTION -> weight in grams
	 */
	protected function LiveAmount() {
		$order = $this->Order();
		$shippingCountry = $this->LiveCountry();
		// if there is a shipping country then check whether it is national or international
		if($shippingCountry) {
			if($shippingCountry == Geoip::$default_country_code) {
				return $this->nationalCost();
			}
			else {
				return $this->internationalCost();
			}
		}
		return 0;
	}

// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)
	protected function IsChargeable() {
		return true;
	}
// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)
// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}

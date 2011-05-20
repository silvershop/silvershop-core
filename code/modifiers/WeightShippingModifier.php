<?php
/**
 * Calculates the shipping cost of an order, by taking the products
 * and calculating the shipping weight, based on an array set in _config
 *
 * ASSUMPTION: The total order weight can be at maximum the last item
 * in the $shippingCosts array.
 *
 * @package ecommerce
 */
class WeightShippingModifier extends OrderModifier {

	/**
	 * Calculates the extra charges from the order based on the weight attribute of a product
 	 * ASSUMPTION -> weight in grams
	 */
	function LiveAmount() {
		$order = $this->Order();

		$orderItems = $order->Items();
		// Calculate the total weight of the order
		if($orderItems) {
			foreach($orderItems as $orderItem) $totalWeight += $orderItem->Weight * $orderItem->quantity;
		}

		// Check if UseShippingAddress is true and if ShippingCountry exists and use that if it does
		if($order->UseShippingAddress && $order->ShippingCountry) $shippingCountry = $order->ShippingCountry;

		// if there is a shipping country then check whether it is national or international
		if($shippingCountry) {
			if($shippingCountry == 'NZ') return $this->nationalCost($totalWeight);
			else return $this->internationalCost($totalWeight, $shippingCountry);
		}
		else {
			if($order->MemberID && $member = DataObject::get_by_id('Member', $order->MemberID)) {
				if($member->Country) $country = $member->Country;
				else $country = Geoip::visitor_country();
			}
			if(! $country) $country = 'NZ';
			if($country == 'NZ') return $this->nationalCost($totalWeight);
			else return $this->internationalCost($totalWeight, $country);
		}
	}

	/**
	 * Retrieve the cost from NZ shipping
	 */
	function nationalCost($totalWeight){
		// if a product can't have a weight, don't charge/display it
		if($totalWeight <= 0) return '0.00';

		// return the pricing appropriate for the weight
		$shippingCosts = self::$a['NZ'];

		return $this->getCostFromWeightList($totalWeight, $shippingCosts);
	}

	/**
	 * Retrieve the cost from overseas shipping
	 */
	function internationalCost($totalWeight, $country){
		// if a product can't have a weight. Don't charge/display it
		if($totalWeight <= 0) return '0.00';

		// return the pricing appropriate for the weight
		$shippingCosts = self::$a[$country];

		// if there isn't any country code specifically in the array, use a zone instead
		if(! $shippingCosts) {
			$zone = self::$b[$country];
			$shippingCosts = self::$a[$zone];
		}
		return $this->getCostFromWeightList($totalWeight, $shippingCosts);
	}

	/**
	 * Get the cost from a list of max-weight => cost pairs
	 */
	function getCostFromWeightList($totalWeight, $shippingCosts) {
		if($shippingCosts) {
			foreach($shippingCosts as $weight => $cost) {
				if($totalWeight < $weight) return $cost;
			}
			return array_pop($shippingCosts);
		}
	}

}

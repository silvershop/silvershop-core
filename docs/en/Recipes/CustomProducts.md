You may need to introduce the ability to buy a different kind of product.
This requires creating both the new type of product, and a likely a new type of order item
to store against the order.

You may want to store details against an order item, which are currently not provided by the base classes.

	class MyOrderItem extends Product_OrderItem{
	
		static $db = array(
			'MyField' => 'Varchar'
		);
		
		static $unique_fields = array(
			'MyField'
		);
	
	}
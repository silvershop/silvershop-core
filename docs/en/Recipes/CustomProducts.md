# Custom Products

You may want to sell things other than generic products within your store. You may need additional features and functionality for these products. You might want to sell something that already exists in your site.

Here are a few options:

 * Customise the Product class(es) using decorators, or modifying core code.
 * Subclass Product to create your own type of product.
 * Turn a dataojbect into something buyable.
 
If you want to sell things that a visitor can choose customisations of, you should consider make use of the product variations system. Alternatively, the module supports creating your own customisations.

In most cases you will need to customise or create your own order items. These record the relationship between a product/buyable and an order.

	:::php
	class MyOrderItem extends OrderItem{
	
		static $db = array(
			'MyField' => 'Varchar'
		);
		
		static $has_one = array(
			'Buyable' => 'MyProduct'
		);
	
	}

	
# Buyables

The concept of something being buyable was introduced to allow things other than Products to be included in the cart. The Buyable interface enforces a few methods that are needed for objects to be added to the shopping cart. Product and ProductVariation are both examples of models implementing the Buyable interface. You can see another example at the bottom of the shop/tests/CustomProductTest.php file.

To make your dataobject buyable:

 * Create/choose the class of what you want to become buyable. It must extend DataObject at some point.
 * Add 'implements Buyable'
 * Introduce the required functions.
 * Extend OrderItem as something like MyBuyable_OrderItem.
 * Specify the buyable relationship on your MyBuyable_OrderItem
 * Create an array of required_fields, which tells the system which fields are unique, and should be
 matched when interacting with the shopping cart.

Completely custom products - every time you add one to cart, it doesn't attempt to combine with existing matches.
Match on the OrderItemId for quantity changes.
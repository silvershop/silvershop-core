# Custom Products

You may want to sell things other than generic products within your store. You may need additional
features and functionality for these products. You might want to sell something that already exists
in your site.

Here are a few options:

 * Customise the Product class(es) using decorators, or modifying core code.
 * Subclass Product to create your own type of product.
 * Turn a dataojbect into something buyable.
 
If you want to sell things that a visitor can choose customisations of, you should make use of
product variations or shopping cart filters.

In most cases you will need to customise or create your own order items. These record the relationship
between a product/buyable and an order.

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

The concept of something being buyable was introduced to allow things other than Products to be
included in the cart. The Buyable interface enforces a few methods that are needed for objects
to be added to the shopping cart.

Every OrderItem must specify a 'Buyable' has_one relationship, this can be of 

By default Product implements Buyable. If you are not worried about custom products, you can simply
treat Buyables as Products.

You can see an example in the shop/tests/CustomProductTest.php file. You can also observe
shop/code/variations/ProductVariation.php as a secondary example.
# Shopping Cart

How it works, and how to extend it.

## Ways to modify the Cart

There are a few different ways to modify the shopping cart contents, each with their own purpose.

 * URL
 * Form
 * API calls

### Modify via URL

There are a number of urls that can be called to make modifications to the cart.
These are served by the cart page controller (`SilverShop\Page\CartPageController`) under your cart page URL — typically `mysite/cart` when the page uses the `cart` URL segment.

Add product (class + ID in the URL — class name is sanitised, e.g. `SilverShop-Page-Product`):

	example.com/cart/add/SilverShop-Page-Product/19

Remove one:

	example.com/cart/remove/SilverShop-Page-Product/19

Remove all:

	example.com/cart/removeall/SilverShop-Page-Product/19
	
Set quantity to 5:

	example.com/cart/setquantity/SilverShop-Page-Product/19?quantity=5

### Modify via form

Cart contents can be modified with a form. This is particularly useful when needign to choose custom options for an order item.

### API calls

The ShoppingCart class can be called directly for your own purposes. See ShoppingCart.php for more details.

For HTTP routes, JSON responses, and the `updateCartItemModification` validation hook, see [Cart API](Cart_API.md).

## Product Versions

When an item is added to the cart, the system also stores the current version associated with that product.
This is done so that the product details (Price, Name, etc) do not change for a customer if the product details are changed by a
store administrator.

Only if a customer completely removes an item from the cart will they be presented with a different set of details.

## Ordering Customised Products (Order Item Parameters)

You may want to add seperate types of the same item to a cart.
for example: "12 meters of rope", along with "5 meters of rope".
or another example: "chicken soup" for delivery this week, along with "chicken soup" for delivery in two weeks

The shop module supports such products.

Making use of this requires:

 * adding GET parameters to url links / forms
 * modifying order item to store data, such as length, or group

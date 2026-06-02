You may want to sell things other than generic products within your store. You may need additional features and functionality for these products. You might want to sell something that already exists as a different kind of `DataObject` in your site.

Here are a few options:

 * Customise the Product class(es) using decorators, or modifying core code.
 * Subclass Product to create your own type of product.
 * Turn a `DataObject` into something `Buyable`.
 
If you want to sell things that a visitor can choose customisations of, you should consider make use of the product variations system. Alternatively, the module supports creating your own customisations.

In most cases you will need to customise or create your own order item class. This class stores any extra
customisation fields and defines which relation on the order item points at your buyable object.

	
# Buyables

The concept of something being buyable was introduced to allow things other than Products to be included in the cart. The Buyable interface enforces the methods required for objects to be added to the shopping cart. Product and ProductVariation are both examples of models implementing the Buyable interface. A full custom example is available in:

- `tests/php/Model/Product/CustomProduct.php`
- `tests/php/Model/Product/CustomProduct_OrderItem.php`
- `tests/php/Model/Product/CustomProductTest.php`

To make your `DataObject` buyable:

 * Create/choose the class of what you want to become buyable. It must extend `DataObject` at some point.
 * Add `implements Buyable`
 * Introduce the functions required by the interface.
 * Extend `SilverShop\Model\Product\OrderItem` (or `SilverShop\Model\OrderItem`) with a custom order item class.
 * Add a `has_one` relation from your custom order item to your custom buyable.
 * Set the order item's `$buyable_relationship` config to that `has_one` relation name.
 * Configure `required_fields` on the order item if item options should control cart uniqueness.
 * You will also need to implement `TableTitle` if you want your custom item to show up in the cart.
 * Implement `Link` if you want the item to link somewhere, such as viewing the buyable on your site.

Example:

```php
class MyBuyable extends DataObject implements Buyable
{
    private static array $order_item = MyBuyableOrderItem::class;

    public function createItem(int $quantity = 1, array $filter = []): \SilverShop\Model\OrderItem
    {
        $item = MyBuyableOrderItem::create();
        $item->MyBuyableID = $this->ID;
        $item->Quantity = $quantity;
        $item->update($filter);

        return $item;
    }
}

class MyBuyableOrderItem extends \SilverShop\Model\Product\OrderItem
{
    private static array $has_one = [
        'MyBuyable' => MyBuyable::class,
    ];

    private static string $buyable_relationship = 'MyBuyable';

    private static array $required_fields = [
        'MyOption',
    ];
}
```

Completely custom products - every time you add one to cart, it doesn't attempt to combine with existing matches. Match on the OrderItemId for quantity changes.
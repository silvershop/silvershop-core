# Cart HTTP API and validation

This document summarises the cart surface shipped with SilverShop core (see GitHub issues [#33](https://github.com/silvershop/silvershop-core/issues/33) and [#90](https://github.com/silvershop/silvershop-core/issues/90)).

## HTTP routes

Cart actions are handled by {@see SilverShop\Page\CartPageController} on the **cart page** URL (see {@see SilverShop\Page\CartPage::find_link()}). With a default install this is typically `/cart/...`; the first segment comes from your `CartPage` record (often `cart`). All actions ultimately delegate to `SilverShop\Cart\ShoppingCart`.

| Action | Typical request | Shopping Cart API |
| ------ | --------------- | ------------------ |
| Add / remove via quantity 0 | `GET …/add/{Buyable}/{ID}?quantity=n` | `add()`, `remove()` |
| Remove one | `GET …/remove/{Buyable}/{ID}` | `remove()` |
| Remove line | `GET …/removeall/{Buyable}/{ID}` | `remove()` |
| Set quantity | `GET …/setquantity/{Buyable}/{ID}?quantity=n` | `setQuantity()` → `updateOrderItemQuantity()` / `remove()` |
| Bulk variations | `POST …/addvariations` (`ProductID`, `VariantQuantity[]`) | `add()` per row |
| Switch variation | `GET` or `POST …/switchvariation?ItemID=&VariationID=` | `switchOrderItemVariation()` |
| Clear cart | `GET …/clear` | `clear()` |

Optional config on `SilverShop\Page\CartPageController`:

- `direct_to_cart_page` — redirect to the cart page after cart mutations (HTML clients).
- `disable_security_token` — omit the security token from generated add/remove/set-quantity URLs (GET cart links only; bulk `addvariations` still validates CSRF when tokens are enabled).

Legacy action names (`additem`, `removeitem`, …) remain in `allowed_actions` for backwards compatibility.

### JSON clients

Pass `?format=json` and/or `Accept: application/json`. Responses include at least:

- `success` (boolean)
- `message` (string) — mirrors `ShoppingCart::getMessage()`
- `messageType` (`good` | `bad` | `warning` / extension‑defined)

Successful mutation responses may add fields such as `itemId` or `variationId`. HTML browsers keep redirects / `HTTPResponse_Exception` semantics via `httpError()`.

Form-based cart edits use `SilverShop\Forms\CartForm`, which calls the same `ShoppingCart` methods (including `switchOrderItemVariation()` when the customer changes the variation dropdown).

## Central validation hook

Every add, quantity change, remove, and variation switch builds a `CartItemModificationContext` and runs:

```php
$order->extend('updateCartItemModification', $context, $outcome);
```

Implement this on a `DataExtension` applied to `SilverShop\Model\Order`. Inspect `SilverShop\Cart\CartItemModificationContext::OP_*` constants for the `operation` field. Populate `SilverShop\Cart\CartItemModificationOutcome` to:

- Set `abort` to `true` and `message` / `messageType` to block the operation (no exception required).
- Set `lineQuantityAfter` to clamp or adjust the line quantity (non‑negative). Applies to add totals, set quantity, remove (remaining quantity), and variation switches.
- Set `message` without `abort` to emit feedback such as warnings when quantities are adjusted.

Older hooks (`beforeAdd`, `afterAdd`, `beforeRemove`, `afterRemove`, `beforeSetQuantity`, `afterSetQuantity`) still run for backwards compatibility; new logic should prefer `updateCartItemModification` for a single validation path.

## Types

- `SilverShop\Cart\CartItemModificationContext`
- `SilverShop\Cart\CartItemModificationOutcome`

Refer to the PHP docblocks on `CartPageController`, `ShoppingCart`, and these classes for parameter semantics.

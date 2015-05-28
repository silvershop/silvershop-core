The shop module allows the checkout to exist as either a single page, or a multi-page solution, and somewhere in-between.

The multi-page solution is known as `Stepped Checkout`. The single page solution utilises `CheckoutForm`. In both cases, you will need to understand what is `CheckoutComponent` .

## Checkout Components

Checkout components are a solution to encapsulate pieces of the checkout, including:

 * Form fields
 * Required form fields
 * Data validation
 * Data Retrieval (getting)
 * Data Storing (setting)

This encapsulation allows for more flexible checkout customisations than a single form.

The checkout component system was inspired by `GridField`'s components.

## Single Step Checkout

A single step checkout is somewhat limited, because it requires the use of ajax to modify the form if one part relies on another.

## Multi Step Checkout

See [Multi Step Checkout](Multi_Step_Checkout.md).

# Multi-Step Checkout

Historically the checkout has only been based on a single OrderForm. Whilst its nice to place an order in a single form submission, in most cases different parts of the checkout process rely on the data of others. In particular, to provide prices for shipping, we first need an address. To do this in a single form, we require the aid of javascript/ajax.

There is a multi-step version of the checkout available that can be enabled. See shop/code/steppedcheckout, which contains a number of decorators for CheckoutPage. Each step is stored in a class with an action to view the step, a form to gather data, and an action to process the form data.

To enable the multi-step checkout, first add this to your _config.php file:
 
```
SteppedCheckout::setupSteps();
```
	
The SteppedCheckout::setupSteps() function adds all of the steps as extensions to CheckoutPage_Controller, and make sure the index action (yoursite.tld/checkout/) is the first step.
 
Next, replace your `CheckoutPage.ss` template with one that uses steps. You can find such a template in `shop/templates/Layout/SteppedCheckoutPage.ss` you could put this in your mysite/templates/Layout folder and rename it to `CheckoutPage.ss`.
 
You may notice that the `SteppedCheckoutPage.ss` template contains statements like:

```
<% if IsFutureStep(contactdetails) %> ... <% end_if %>
<% if IsCurrentStep(contactdetails) %> ... <% end_if %>
<% if IsPastStep(contactdetails) %> ... <% end_if %>
```

These functions have been set up to enable a single page template to handle multiple actions. This approach is good for showing upcoming steps, the current form, and past data that the user has entered already.

You can also define individual templates if you like, eg:

```
mysite/templates/Layout/CheckoutPage_contactdetails.ss
mysite/templates/Layout/CheckoutPage_shippingaddress.ss
mysite/templates/Layout/CheckoutPage_billingaddress.ss
mysite/templates/Layout/CheckoutPage_paymentmethod.ss
mysite/templates/Layout/CheckoutPage_summary.ss
```
(templates could also be in your theme)

## Configuring Steps

Steps can be configured using yaml config:

```
CheckoutPage:
    steps:
        'membership' : 'CheckoutStep_Membership'
        'contactdetails' : 'CheckoutStep_ContactDetails'
        'shippingaddress' : 'CheckoutStep_Address'
        'billingaddress' : 'CheckoutStep_Address'
        'paymentmethod' : 'CheckoutStep_PaymentMethod'
        'summary' : 'CheckoutStep_Summary'
```

Add/remove/reorder steps as you please, but keep in mind that the data from one step may be reliant on data from another.
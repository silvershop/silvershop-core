<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1 class="silvershop-pageTitle">$Title</h1>
<div id="Checkout">
    <div class="silvershop-typography">

        <% if $PaymentErrorMessage %>
            <p class="silvershop-message silvershop-error">
            <%t SilverShop\Page\CheckoutPage.PaymentErrorMessage 'Received error from payment gateway:' %>
            $PaymentErrorMessage
            </p>
        <% end_if %>

        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <% if $Cart %>
        <% with $Cart %>
            <% include SilverShop\Cart\Cart ShowSubtotals=true %>
        <% end_with %>
        $OrderForm
    <% else %>
        <p class="silvershop-message silvershop-warning"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
</div>

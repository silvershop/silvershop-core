<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
    <div class="typography">

        <% if $PaymentErrorMessage %>
            <p class="message error">
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
        <p class="message warning"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
</div>

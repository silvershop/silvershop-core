<% require themedCSS(checkout,shop) %>
<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
    <div class="typography">

        <% if $PaymentErrorMessage %>
            <p class="message error">
            <%t CheckoutPage.PaymentErrorMessage 'Received error from payment gateway:' %>
            $PaymentErrorMessage
            </p>
        <% end_if %>

        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <% if $Cart %>
        <% with $Cart %>
            <% include Cart ShowSubtotals=true %>
        <% end_with %>
        $OrderForm
    <% else %>
        <p class="message warning"><%t ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
</div>

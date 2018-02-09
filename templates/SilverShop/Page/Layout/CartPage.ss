<% require css("silvershop/core: client/dist/css/cart.css") %>

<h1 class="pagetitle">$Title</h1>
<div class="typography">
    <% if $Content %>
        $Content
    <% end_if %>
</div>
<% if $Cart %>

    <% if $CartForm %>
        $CartForm
    <% else %>
        <% with $Cart %><% include SilverShop\Cart\Cart Editable=true %><% end_with %>
    <% end_if %>

<% else %>
    <p class="message warning"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
<% end_if %>
<div class="cartfooter">
    <% if $ContinueLink %>
        <a class="continuelink button" href="$ContinueLink">
            <%t SilverShop\Cart\ShoppingCart.ContinueShopping 'Continue Shopping' %>
        </a>
    <% end_if %>
    <% if $Cart %>
        <% if $CheckoutLink %>
            <a class="checkoutlink button" href="$CheckoutLink">
                <%t SilverShop\Cart\ShoppingCart.ProceedToCheckout 'Proceed to Checkout' %>
            </a>
        <% end_if %>
    <% end_if %>
</div>

<% require css("silvershop/core: client/dist/css/cart.css") %>

<div class="silvershop-cart-page silvershop-typography">
    <h1 class="silvershop-cart-page__title">$Title</h1>
    <div class="silvershop-cart-page__content">
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
        <p class="silvershop-message silvershop-message--warning"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
    <div class="silvershop-cart-page__footer">
        <% if $ContinueLink %>
            <a class="silvershop-cart-page__continue silvershop-button" href="$ContinueLink">
                <%t SilverShop\Cart\ShoppingCart.ContinueShopping 'Continue Shopping' %>
            </a>
        <% end_if %>
        <% if $Cart %>
            <% if $CheckoutLink %>
                <a class="silvershop-cart-page__checkout silvershop-button" href="$CheckoutLink">
                    <%t SilverShop\Cart\ShoppingCart.ProceedToCheckout 'Proceed to Checkout' %>
                </a>
            <% end_if %>
        <% end_if %>
    </div>
</div>

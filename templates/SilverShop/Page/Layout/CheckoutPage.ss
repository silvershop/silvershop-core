<% if $SilverShopIncludeDefaultStyles %>
<% require css("silvershop/core:client/dist/css/silvershop-base.css") %>
<% require css("silvershop/core:client/dist/css/checkout.css") %>
<% end_if %>

<div class="silvershop-checkout">
    <h1 class="silvershop-checkout__title">$Title</h1>
    <div class="silvershop-checkout__content silvershop-typography">

        <% if $PaymentErrorMessage %>
            <p class="silvershop-message silvershop-message--error">
                <%t SilverShop\Page\CheckoutPage.PaymentErrorMessage 'Received error from payment gateway:' %>
                $PaymentErrorMessage
            </p>
        <% end_if %>

        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <% if $Cart %>
        <div class="silvershop-checkout__columns">
            <div class="silvershop-checkout__column silvershop-checkout__column--summary">
                <% with $Cart %>
                    <% include SilverShop\Cart\Cart ShowSubtotals=true %>
                <% end_with %>
            </div>
            <div class="silvershop-checkout__column silvershop-checkout__column--form">
                <div class="silvershop-checkout__form">
                    $OrderForm
                </div>
            </div>
        </div>
    <% else %>
        <p class="silvershop-message silvershop-message--warning"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></p>
    <% end_if %>
</div>

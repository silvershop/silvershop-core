<% require css("silvershop/core: client/dist/css/account.css") %>

<% include SilverShop\Includes\AccountNavigation %>
<div class="silvershop-account silvershop-typography">
    <div class="silvershop-account__content">$Content</div>
    <h2 class="silvershop-account__title"><%t SilverShop\Page\AccountPage.PastOrders 'Past Orders' %></h2>
    <% with $Member %>
        <% if $PastOrders %>
            <% include SilverShop\Includes\OrderHistory %>
        <% else %>
            <p class="silvershop-message silvershop-message--warning"><%t SilverShop\Page\AccountPage.NoPastOrders 'No past orders found.' %></p>
        <% end_if %>
    <% end_with %>
</div>

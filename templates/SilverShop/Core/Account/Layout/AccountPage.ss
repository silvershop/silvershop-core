<% require css("silvershop/core: css/account.css") %>
<% require themedCSS("shop") %>
<% require themedCSS("account") %>

<% include SilverStripe\Core\Account\AccountNavigation %>
<div id="Account" class="typography">
    $Content
    <h2 class="pagetitle"><%t SilverShop\Page\AccountPage.PastOrders 'Past Orders' %></h2>
    <% with $Member %>
        <% if $PastOrders %>
            <% include SilverShop\OrderHistory %>
        <% else %>
            <p class="message warning"><%t SilverShop\Page\AccountPage.NoPastOrders 'No past orders found.' %></p>
        <% end_if %>
    <% end_with %>
</div>

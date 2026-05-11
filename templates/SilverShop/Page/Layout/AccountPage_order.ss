<% if $SilverShopIncludeDefaultStyles %>
<% require css("silvershop/core:client/dist/css/silvershop-base.css") %>
<% require css("silvershop/core:client/dist/css/account.css") %>
<% end_if %>

<% include SilverShop\Includes\AccountNavigation %>
<div class="silvershop-account silvershop-account--order silvershop-typography">
    <% if $Order %>
        <% with $Order %>
            <h2 class="silvershop-account__order-headline"><%t SilverShop\Model\Order.OrderHeadline "Order #{OrderNo} {OrderDate}" OrderNo=$Reference OrderDate=$Created.Nice %></h2>
        <% end_with %>
    <% end_if %>
    <% if $Message %>
        <p class="silvershop-message silvershop-message--$MessageType">$Message</p>
    <% end_if %>
    <% if $Order %>
        <% with $Order %>
            <% include SilverShop\Model\Order %>
        <% end_with %>
        $ActionsForm
    <% end_if %>
</div>

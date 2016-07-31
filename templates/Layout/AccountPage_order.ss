<% require css("silvershop/css/account.css") %>
<% require themedCSS("shop") %>
<% require themedCSS("account") %>
<% include AccountNavigation %>
<div id="Account" class="typography">
    <% if $Order %>
        <% with $Order %>
            <h2><%t Order.OrderHeadline "Order #{OrderNo} {OrderDate}" OrderNo=$Reference OrderDate=$Created.Nice %></h2>
        <% end_with %>
    <% end_if %>
    <% if $Message %>
        <p class="message $MessageType">$Message</p>
    <% end_if %>
    <% if $Order %>
        <% with $Order %>
            <% include Order %>
        <% end_with %>
        $ActionsForm
    <% end_if %>
</div>

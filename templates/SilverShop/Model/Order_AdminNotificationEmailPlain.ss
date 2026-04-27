<%t SilverShop\ShopEmail.AdminNotificationTitle "Shop Receipt" %>

<% if $Order %>
<% with $Order %>
<% include SilverShop\Model\Order_EmailPlainBody %>
<% end_with %>
<% end_if %>

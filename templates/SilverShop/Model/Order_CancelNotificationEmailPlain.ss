<%t SilverShop\ShopEmail.CancelTitle "Order Cancelled" %>

<% if $Order %>
<% with $Order %>
<%t SilverShop\ShopEmail.CancelNotice 'Order #{OrderNo} has been cancelled by the customer.' OrderNo=$Reference %>

<% include SilverShop\Model\Order_EmailPlainBody %>
<% end_with %>
<% end_if %>

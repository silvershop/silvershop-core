<%t SilverShop\ShopEmail.ConfirmationTitle "Order Confirmation" %>

$PurchaseCompleteMessage

<% if $Order %>
<% with $Order %>
<% include SilverShop\Model\Order_EmailPlainBody %>
<% end_with %>
<% end_if %>

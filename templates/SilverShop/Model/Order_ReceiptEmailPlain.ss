<%t SilverShop\ShopEmail.ReceiptTitle "Shop Receipt" %>

$PurchaseCompleteMessage

<% if $Order %>
<% with $Order %>
<% include SilverShop\Model\Order_EmailPlainBody %>
<% end_with %>
<% end_if %>

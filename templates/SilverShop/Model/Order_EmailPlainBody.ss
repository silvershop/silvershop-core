<%t SilverShop\Model\Order.db_Reference "Reference" %>: $Reference
<%t SilverShop\Model\Order.db_Status "Status" %>: $StatusI18N
<% if $Placed %><%t SilverShop\Model\Order.db_Placed "Placed" %>: $Placed.Nice
<% end_if %>

<%t SilverShop\Model\Order.ShipTo "Ship To" %>:
<% if $getShippingAddress && $getShippingAddress.exists %>$getShippingAddress.toString
<% else %>—
<% end_if %>

<%t SilverShop\Model\Order.BillTo "Bill To" %>:
<% if $getBillingAddress && $getBillingAddress.exists %>$getBillingAddress.toString
<% else %>—
<% end_if %>

<%t SilverShop\Model\OrderItem.PLURALNAME "Items" %>:
<% loop $Items %>- $TableTitle<% if $SubTitle %> ($SubTitle)<% end_if %>
  <%t SilverShop\Model\Order.UnitPrice "Unit Price" %>: $UnitPrice.Nice | <%t SilverShop\Model\Order.Quantity "Quantity" %>: $Quantity | <%t SilverShop\Model\Order.TotalPriceWithCurrency "Total Price ({Currency})" Currency=$Currency %>: $Total.Nice

<% end_loop %>
<%t SilverShop\Model\Order.SubTotal "Sub-total" %>: $SubTotal.Nice
<% loop $Modifiers %><% if $ShowInTable %>$TableTitle: $TableValue.Nice
<% end_if %><% end_loop %>
<%t SilverShop\Model\Order.Total "Total" %>: $Total.Nice $Currency
<% if $Total %><% if $Payments %>
<%t SilverShop\Payment.PaymentsHeadline "Payment(s)" %>:
<% loop $Payments %>  $Created.Nice | $Amount.Nice $Currency | $PaymentStatus | $GatewayTitle
<% end_loop %>
<% end_if %>
<%t SilverShop\Model\Order.TotalOutstanding "Total outstanding" %>: $TotalOutstanding.Nice
<% end_if %>
<% if $Notes %>
<%t SilverShop\Model\Order.db_Notes "Notes" %>:
$Notes
<% end_if %>

<table id="AddressesTable" class="infotable">
	<tr>
		<th scope="col"><% _t("Order.PURCHASEDBY","Purchased by") %></th>
		<% if ShippingAddress %><th scope="col"><% _t("Order.SHIPTO","Ship To") %></th><% end_if %>
	</tr>
	<tr>
		<td><% include Order_AddressBilling %></td>
		<% if ShippingAddress %><td><% include Order_AddressShipping %></td><% end_if %>
	</tr>
</table>

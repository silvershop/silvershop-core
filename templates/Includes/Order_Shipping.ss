<table id="ShippingTable" class="infotable">
	<tr>
		<th><% _t("PURCHASEDBY","Purchased by") %></th>
		<% if UseShippingAddress %><th><% _t("SHIPTO","Ship To") %></th><% end_if %>
	</tr>
	<tr>
		<td>$FullBillingAddress</td>
		<% if UseShippingAddress %><td>$FullShippingAddress</td><% end_if %>
	</tr>
</table>

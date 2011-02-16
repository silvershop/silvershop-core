<% if ShippingAddress %> <% control ShippingAddress %>
<table class="addressTable" cellspacing="0" cellpadding="0" id="ShippingAddressAddressTable">
	<tr id="ShippingAddressName">
		<th scope="row"><% _t("NAME","Name") %></th>
		<td>$ShippingName</td>
	</tr>
	<% if ShippingAddress %>
	<tr id="ShippingAddressAddress">
		<th scope="row"><% _t("ADDRESS","Address") %></th>
		<td>$ShippingAddress<% if AddressLine2 %><br/>$ShippingAddress2<% end_if %></td>
	</tr>
	<% end_if %>
	<% if ShippingCity %>
		<tr id="ShippingAddressCity">
			<th scope="row"><% _t("CITY","City") %></th>
			<td>$ShippingCity</td>
		</tr>
	<% end_if %>
	<% if ShippingState %>
		<tr id="ShippingAddressState">
			<th scope="row"><% _t("STATE","State") %></th>
			<td>$ShippingState</td>
		</tr>
	<% end_if %>
	<% if ShippingPostalCode %>
		<tr id="ShippingAddressPostalCode">
			<th scope="row"><% _t("POSTALCODE","Postal Code") %></th>
			<td>$ShippingPostalCode</td>
		</tr>
	<% end_if %>
	<% if ShippingCountry %>
	<tr id="ShippingAddressCountry">
		<th scope="row"><% _t("COUNTRY","Country") %></th>
		<td>$ShippingCountry</td>
	</tr>
	<% end_if %>
	<% if ShippingPhone %>
	<tr id="ShippingAddressPhone">
		<th scope="row"><% _t("PHONE","Phone") %></th>
		<td>$ShippingPhone</td>
	</tr>
	<% end_if %>
</table>
<% end_control %>
<% end_if %>

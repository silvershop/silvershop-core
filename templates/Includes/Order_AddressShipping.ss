<% if ShippingAddress %> <% control ShippingAddress %>
<table class="addressTable" cellspacing="0" cellpadding="0" id="ShippingAddressTable">
	<tr id="ShippingName">
		<th scope="row"><% _t("NAME","Name") %></th>
		<td>$ShippingName</td>
	</tr>
	<% if ShippingAddress %>
	<tr id="ShippingAddress">
		<th scope="row"><% _t("ADDRESS","Address") %></th>
		<td>$ShippingAddress<% if AddressLine2 %><br/>$ShippingAddress2<% end_if %></td>
	</tr>
	<% end_if %>
	<% if ShippingCity %>
		<tr id="ShippingCity">
			<th scope="row"><% _t("CITY","City") %></th>
			<td>$ShippingCity</td>
		</tr>
	<% end_if %>
	<% if ShippingState %>
		<tr id="ShippingState">
			<th scope="row"><% _t("STATE","State") %></th>
			<td>$ShippingState</td>
		</tr>
	<% end_if %>
	<% if ShippingPostalCode %>
		<tr id="ShippingPostalCode">
			<th scope="row"><% _t("POSTALCODE","Postal Code") %></th>
			<td>$ShippingPostalCode</td>
		</tr>
	<% end_if %>
	<% if ShippingCountry %>
	<tr id="ShippingCountry">
		<th scope="row"><% _t("COUNTRY","Country") %></th>
		<td>$ShippingCountry</td>
	</tr>
	<% end_if %>
	<% if ShippingPhone %>
	<tr id="ShippingPhone">
		<th scope="row"><% _t("PHONE","Phone") %></th>
		<td>$ShippingPhone</td>
	</tr>
	<% end_if %>
</table>
<% end_control %>
<% end_if %>

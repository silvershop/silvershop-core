<% control Member %>
<table class="addressTable" cellspacing="0" cellpadding="0" id="PurchaserAddressTable">
	<tr id="AddressBillingName">
		<th scope="row"><% _t("NAME","Name") %></th>
		<td>$Name</td>
	</tr>
	<% if Address %>
	<tr id="AddressBillingAddress">
		<th scope="row"><% _t("ADDRESS","Address") %></th>
		<td>$Address<% if AddressLine2 %><br/>$Address2<% end_if %></td>
	</tr>
	<% end_if %>
	<% if City %>
		<tr id="AddressBillingCity">
			<th scope="row"><% _t("CITY","City") %></th>
			<td>$City</td>
		</tr>
	<% end_if %>
	<% if State %>
		<tr id="AddressBillingState">
			<th scope="row"><% _t("STATE","State") %></th>
			<td>$State</td>
		</tr>
	<% end_if %>
	<% if PostalCode %>
		<tr id="AddressBillingPostalCode">
			<th scope="row"><% _t("POSTALCODE","Postal Code") %></th>
			<td>$PostalCode</td>
		</tr>
	<% end_if %>
	<% if Country %>
	<tr id="AddressBillingCountry">
		<th scope="row"><% _t("COUNTRY","Country") %></th>
		<td>$Country</td>
	</tr>
	<% end_if %>
	<% if Phone %>
	<tr id="AddressBillingPhone">
		<th scope="row"><% _t("PHONE","Phone") %></th>
		<td>$Phone</td>
	</tr>
	<% end_if %>
	<% if Email %>
	<tr id="AddressBillingEmail">
		<th scope="row"><% _t("EMAIL","Email") %></th>
		<td>$Email</td>
	</tr>
	<% end_if %>
</table>
<% end_control %>

<% control Member %>
<table class="addressTable" cellspacing="0" cellpadding="0" id="PurchaserAddressTable">
	<tr id="Name">
		<th scope="row"><% _t("NAME","Name") %></th>
		<td>$Name</td>
	</tr>
	<% if Address %>
	<tr id="Address">
		<th scope="row"><% _t("ADDRESS","Address") %></th>
		<td>$Address<% if AddressLine2 %><br/>$Address2<% end_if %></td>
	</tr>
	<% end_if %>
	<% if City %>
		<tr id="City">
			<th scope="row"><% _t("CITY","City") %></th>
			<td>$City</td>
		</tr>
	<% end_if %>
	<% if State %>
		<tr id="State">
			<th scope="row"><% _t("STATE","State") %></th>
			<td>$State</td>
		</tr>
	<% end_if %>
	<% if PostalCode %>
		<tr id="PostalCode">
			<th scope="row"><% _t("POSTALCODE","Postal Code") %></th>
			<td>$PostalCode</td>
		</tr>
	<% end_if %>
	<% if Country %>
	<tr id="Country">
		<th scope="row"><% _t("COUNTRY","Country") %></th>
		<td>$Country</td>
	</tr>
	<% end_if %>
	<% if Phone %>
	<tr id="Phone">
		<th scope="row"><% _t("PHONE","Phone") %></th>
		<td>$Phone</td>
	</tr>
	<% end_if %>
	<% if Email %>
	<tr id="Email">
		<th scope="row"><% _t("EMAIL","Email") %></th>
		<td>$Email</td>
	</tr>
	<% end_if %>
</table>
<% end_control %>

<table class="address" cellspacing="0" cellpadding="0">
	<tr>
		<th><% _t("NAME","Name") %></th>
		<td>$FirstName $Surname</td>
	</tr>
	<tr>
		<th><% _t("ADDRESS","Address") %></th>
		<td>$Address<% if AddressLine2 %><br/>$AddressLine2<% end_if %></td>
	</tr>
	<tr>
		<th><% _t("CITY","City") %></th>
		<td>$City</td>
	</tr>
	<tr>
		<th><% _t("COUNTRY","Country") %></th>
		<td>$CountryTitle</td>
	</tr>
	<tr>
		<th><% _t("PHONE","Phone") %></th>
		<td>$HomePhone</td>
	</tr>
	<tr>
		<th><% _t("MOBILE","Mobile") %></th>
		<td>$MobilePhone</td>
	</tr>
	<tr>
		<th><% _t("EMAIL","Email") %></th>
		<td>$Email</td>
	</tr>
</table>
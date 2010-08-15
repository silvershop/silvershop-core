<% control Member %>
	<tr class="gap mainHeader">
		<th colspan="4" class="left"><h2><% _t("CUSTOMERDETAILS","Customer Details") %></h2></th>
	</tr>
	<tr class="summary odd first">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("NAME","Name") %></th>
		<td colspan="2" class="price">$FirstName $Surname</td>
	</tr>
	<% if HomePhone %>
	<tr class="summary even">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("PHONE","Phone") %></th>
		<td colspan="2" class="price">$HomePhone</td>
	</tr>
	<% end_if %>
	<% if MobilePhone %>
	<tr class="summary odd">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("MOBILE","Mobile") %></th>
		<td colspan="2" class="price">$MobilePhone</td>
	</tr>
	<% end_if %>
	<tr class="summary even">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("EMAIL","Email") %></th>
		<td colspan="2" class="price">$Email</td>
	</tr>
	<tr class="summary odd">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("BUYERSADDRESS","Buyer's Address") %></th>
		<td colspan="2" class="price">$Address</td>
	</tr>
	<% if AddressLine2 %>
	<tr class="summary even">
		<th colspan="2" scope="row" class="twoColHeader">&nbsp;</th>
		<td colspan="2" class="price">$AddressLine2</td>
	</tr>
	<% end_if %>
	<tr class="summary odd">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("CITY","City") %></th>
		<td colspan="2" class="price">$City</td>
	</tr>
	<% if Country %>
	<tr class="summary even last">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("COUNTRY","Country") %></th>
		<td colspan="2" class="price">$CountryTitle</td>
	</tr>
	<% end_if %>
<% end_control %>




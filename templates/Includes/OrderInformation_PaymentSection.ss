<% control Payments %>
	<tr class="gap mainHeader">
		<th colspan="4" class="left"><h2><% _t("PAYMENTINFORMATION","Payment Information") %></h2></th>
	</tr>
	<tr class="summary odd first">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("DATE","Date") %></th>
		<td colspan="2" class="price">$LastEdited.Nice</td>
	</tr>
	<tr class="summary even">
		<th colspan="2" scope="row"  class="twoColHeader"><% _t("AMOUNT","Amount") %></th>
		<td colspan="2" class="price">$Amount.Nice $Currency</td>
	</tr>
	<tr class="summary odd">
		<th colspan="2" scope="row"  class="twoColHeader"><% _t("PAYMENTSTATUS","Payment Status") %></th>
		<td colspan="2" class="price">$Status</td>
	</tr>

	<% if PaymentMethod %>
	<tr class="summary even">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("PAYMENTMETHOD","Method") %></th>
		<td colspan="2" class="price">$PaymentMethod - $IP $ProxyIP</td>
	</tr>
	<% end_if %>

	<% if Message %>
	<tr class="summary odd last">
		<th colspan="2" scope="row" class="twoColHeader"><% _t("PAYMENTNOTE","Note") %></th>
		<td colspan="2" class="price">$Message.NoHTML</td>
	</tr>
	<% end_if %>
<% end_control %>

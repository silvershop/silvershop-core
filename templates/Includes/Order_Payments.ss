<table id="PaymentTable" class="infotable">
	<thead>			
		<tr class="gap mainHeader">
				<th colspan="10" class="left"><% _t("PAYMENTS","Payment(s)") %></th>
		</tr>
		<tr>
			<th scope="row" class="twoColHeader"><% _t("DATE","Date") %></th>
			<th scope="row"  class="twoColHeader"><% _t("AMOUNT","Amount") %></th>
			<th scope="row"  class="twoColHeader"><% _t("PAYMENTSTATUS","Payment Status") %></th>
			<th scope="row" class="twoColHeader"><% _t("PAYMENTMETHOD","Method") %></th>
			<th scope="row" class="twoColHeader"><% _t("PAYMENTNOTE","Note") %></th>
		</tr>
	</thead>
	<tbody>
		<% control Payments %>	
			<tr>
				<td class="price">$LastEdited.Nice</td>
				<td class="price">$Amount.Nice $Currency</td>
				<td class="price">$Status</td>
				<td class="price">$PaymentMethod - $IP $ProxyIP</td>
				<td class="price">$Message.NoHTML</td>
			</tr>
		<% end_control %>
	</tbody>
</table>
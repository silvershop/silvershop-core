<% if Payments %>
<table id="OrderStatusLogTable" class="infotable">
	<thead>
		<tr class="gap mainHeader">
			<th colspan="5" class="left"><% _t("PAYMENTS","Payment(s)") %></th>
		</tr>
		<tr>
			<th scope="col" class="center"><% _t("DATE","Date") %></th>
			<th scope="col" class="center"><% _t("PAYMENTSTATUS","Payment Status") %></th>
			<th scope="col" class="center"><% _t("PAYMENTMETHOD","Method") %></th>
			<th scope="col" class="center"><% _t("PAYMENTNOTE","Note") %></th>
			<th scope="col" class="center"><% _t("AMOUNT","Amount") %></th>
		</tr>
	</thead>
	<tbody>
	<% control Payments %>
		<tr>
			<td class="center">$LastEdited.Nice24</td>
			<td class="center">$Status</td>
			<td class="center">$PaymentMethod</td>
			<td class="left">$Message.NoHTML</td>
			<td class="right">$Amount.Nice $Currency</td>
		</tr>
	<% end_control %>
	</tbody>
</table>
<% else %>
<p id="NoPaymentsNote"><% _t("NOPAYMENTS","There are no payments for this order.") %></p>
<% end_if %>

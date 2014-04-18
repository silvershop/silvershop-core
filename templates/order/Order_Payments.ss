<<<<<<< HEAD:templates/order/Order_Payments.ss
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
		<% loop Payments %>	
			<tr>
				<td class="price">$Created.Nice</td>
				<td class="price">$Amount.Nice $Currency</td>
				<td class="price">$Status</td>
				<td class="price">$Gateway</td>
				<td class="price">$Message.NoHTML</td>
			</tr>
			<% if ShowMessages %>
				<% loop Messages %>
					<tr>
						<td colspan="5">
							$ClassName $Message $User.Name
						</td>
					</tr>
				<% end_loop %>
			<% end_if %>
		<% end_loop %>
	</tbody>
=======
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
				<td class="price">$Created.Nice24</td>
				<td class="price">$Amount.Nice $Currency</td>
				<td class="price">$Status</td>
				<td class="price">$PaymentMethod</td>
				<td class="price">$Message.NoHTML</td>
			</tr>
		<% end_control %>
	</tbody>
>>>>>>> 362a57f26a52f44a22c97384eaa84be77c68a508:templates/Includes/Order_Payments.ss
</table>
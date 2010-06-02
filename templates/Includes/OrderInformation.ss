<table id="InformationTable" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th scope="col" class="left"><% _t("PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("PRICE","Price") %> ($Currency)</th>
			<th scope="col" class="right"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% control Items %>
			<tr id="$IDForTable" class="$ClassForTable">
				<td class="product title" scope="row">
					<% if Link %>
						<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">$ProductTitle</a>
					<% else %>
						$ProductTitle
					<% end_if %>
				</td>
				<td class="center quantity">$Quantity</td>
				<td class="right unitprice">$UnitPrice.Nice</td>
				<td class="right total" id="$TotalIDForTable">$Total.Nice</td>
			</tr>
		<% end_control %>
		
		<tr class="gap summary">
			<td colspan="2" scope="row"><% _t("SUBTOTAL","Sub-total") %></td>
			<td>&nbsp;</td>
			<td class="right" id="$SubTotalIDForTable">$SubTotal.Nice</td>
		</tr>

		<% control Modifiers %>
			<% if ShowInOrderTable %>
				<tr id="$IDForTable" class="$ClassForTable">
					<td colspan="2" scope="row" id="$TitleIdForTable">$TitleForTable</td>
					<td>&nbsp;</td>
					<td class="right" id="$ValueIdForTable">$ValueForTable</td>
				</tr>
			<% end_if %>
		<% end_control %>
				
		<tr class="gap Total">
			<td colspan="2" scope="row"><% _t("TOTAL","Total") %></td>
			<td>&nbsp;</td>
			<td class="right" id="$TotalIDForTable">$Total.Nice $Currency</td>
		</tr>
		
		<% control Payment %>
			<tr class="gap">
				<td colspan="4" scope="row" class="left ordersummary"><h3><% _t("ORDERSUMMARY","Order Summary") %>:</h3></td>
			</tr>
		<tr class="gap">
			<th colspan="4" scope="row" class="left"><% _t("PAYMENTINFORMATION","Payment Information") %></th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("PAYMENTID","Payment ID") %></td>
			<td class="price">#$ID</td>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("DATE","Date") %></td>
			<td class="price">$LastEdited.Nice</td>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("AMOUNT","Amount") %></td>
			<td class="price">$Amount.Nice $Currency</td>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("PAYMENTSTATUS","Payment Status") %></td>
			<td class="price">$Status</td>
		</tr>
		<% if PaymentMethod %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left"><% _t("PAYMENTMETHOD","Method") %></td>
				<td class="price">$PaymentMethod</td>
			</tr>
		<% end_if %>
		<% if Message %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left"><% _t("DETAILS","Details") %></td>
				<td class="price">$Message</td>
			</tr>
		<% end_if %>
	<% end_control %>
	<tr class="gap Total">
		<td colspan="3" scope="row" class="left"><strong><% _t("TOTALOUTSTANDING","Total outstanding") %></strong></td>
		<td class="price"><strong>$TotalOutstanding.Nice </strong></td>
	</tr>
	
	<% control Member %>
		<tr class="gap">
			<th colspan="4" scope="row" class="left"><% _t("CUSTOMERDETAILS","Customer Details") %></th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("NAME","Name") %></td>
			<td class="price">$FirstName $Surname</td>
		</tr>
		<% if HomePhone %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left"><% _t("PHONE","Phone") %></td>
				<td class="price">$HomePhone</td>
			</tr>
		<% end_if %>
		<% if MobilePhone %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left"><% _t("MOBILE","Mobile") %></td>
				<td class="price">$MobilePhone</td>
			</tr>
		<% end_if %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("EMAIL","Email") %></td>
			<td class="price">$Email</td>
		</tr>				
		<tr class="gap">
			<th colspan="4" scope="row" class="left"><% _t("ADDRESS","Address") %></th>
		</tr>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("BUYERSADDRESS","Buyer's Address") %></td>
			<td class="price">$Address</td>
		</tr>
		<% if AddressLine2 %>
			<tr class="summary">
				<td colspan="3" scope="row" class="left"></td>
				<td class="price">$AddressLine2</td>
			</tr>
		<% end_if %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("CITY","City") %></td>
			<td class="price">$City</td>
		</tr>
		<% if Country %>
		<tr class="summary">
			<td colspan="3" scope="row" class="left"><% _t("COUNTRY","Country") %></td>
			<td class="price">$CountryTitle</td>
		</tr>
		<% end_if %>
	<% end_control %>

	<% if UseShippingAddress %>
		<tr class="gap shippingDetails">
			<th colspan="4" scope="row" class="left"><% _t("SHIPPINGDETAILS","Shipping Details") %></th>
		</tr>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left"><% _t("NAME","Name") %></td>
			<td class="price">$ShippingName</td>
		</tr>
		<% if ShippingAddress %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left"><% _t("ADDRESS","Address") %></td>
			<td class="price">$ShippingAddress</td>
		</tr>
		<% end_if %>
		<% if ShippingAddress2 %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left"></td>
			<td colspan="3" class="price">$ShippingAddress2</td>
		</tr>
		<% end_if %>
		<% if ShippingCity %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left"><% _t("CITY","City") %></td>
			<td class="price">$ShippingCity</td>
		</tr>
		<% end_if %>
		<% if ShippingCountry %>
		<tr class="summary shippingDetails">
			<td colspan="3" scope="row" class="left"><% _t("COUNTRY","Country") %></td>
			<td class="price">$findShippingCountry</td>
		</tr>
		<% end_if %>
	<% end_if %>
	
	</tbody>
</table>

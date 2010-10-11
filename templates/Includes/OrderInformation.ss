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
			<td colspan="3" scope="row"><% _t("SUBTOTAL","Sub-total") %></td>
			<td class="right" id="$SubTotalIDForTable">$SubTotal.Nice</td>
		</tr>
		
		<% control Modifiers %>
			<tr id="$IDForTable" class="$ClassForTable">
				<td colspan="3" scope="row" id="$TitleIdForTable">$TableTitle</td>
				<td class="right" id="$ValueIdForTable">$TableValue.Nice</td>
			</tr>
		<% end_control %>

		<tr class="gap Total">
			<td colspan="3" scope="row"><% _t("TOTAL","Total") %></td>
			<td class="right" id="$TotalIDForTable">$Total.Nice $Currency</td>
		</tr>
		
	<% include OrderInformation_PaymentSection %>

	<tr class="gap Total">
		<td colspan="3" scope="row" class="left"><strong><% _t("TOTALOUTSTANDING","Total outstanding") %></strong></td>
		<td class="price"><strong>$TotalOutstanding.Nice </strong></td>
	</tr>
	
	<% include OrderInformation_MemberSection %>
	
	<% include OrderInformation_ShippingSection %>
	
	<% if CustomerOrderNote %>
			<tr class="gap mainHeader">
				<th colspan="4" class="left" scope="col"><h2><% _t("CUSTOMERORDERNOTE","Customer Note") %></h2></th>
			</tr>
			<tr class="summary odd first">
				<td colspan="4" class="left fourRolDetail">$CustomerOrderNote</td>
			</tr>
	<% end_if %>

	</tbody>
</table>

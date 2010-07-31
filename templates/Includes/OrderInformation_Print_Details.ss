<div id="OrderInformation">
<table id="InformationTable">
	<thead>
		<tr class="mainHeader">
			<th class="left" colspan="4"><h2>Sales</h2></th>
		</tr>
		<tr>
			<th scope="col" class="left"><% _t("PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("PRICE","Price") %> ($Currency)</th>
			<th scope="col" class="right"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
		</tr>
	</thead>
	<tbody>
		<% control Items %>
		<tr  class="itemRow $EvenOdd $FirstLast">
			<td class="product title" scope="row">
				<% if Link %>
					<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">$ProductTitle</a>
				<% else %>
					$ProductTitle
				<% end_if %>
			</td>
			<td class="center quantity">$Quantity</td>
			<td class="right unitprice">$UnitPrice.Nice</td>
			<td class="right total">$Total.Nice</td>
		</tr>
		<% end_control %>

		<tr class="gap summary" id="SubTotal">
			<th colspan="3" scope="row" class="threeColHeader"><% _t("SUBTOTAL","Sub-total") %></th>
			<td class="right">$SubTotal.Nice</td>
		</tr>

		<% control Modifiers %>
			<% if ShowInTable %>
		<tr class="modifierRow $EvenOdd $FirstLast">
			<td colspan="3" scope="row">$TableTitle</td>
			<td class="right">$TableValue</td>
		</tr>
			<% end_if %>
		<% end_control %>

		<tr class="gap summary" id="Total">
			<th colspan="3" scope="row" class="threeColHeader"><% _t("TOTAL","Total") %></th>
			<td class="right">$Total.Nice $Currency</td>
		</tr>

<% include OrderInformation_PaymentSection %>

		<tr class="gap summary" id="Outstanding">
			<th colspan="3" scope="row" class="threeColHeader"><strong><% _t("TOTALOUTSTANDING","Total outstanding") %></strong></th>
			<td class="right"><strong>$TotalOutstanding.Nice </strong></td>
		</tr>

<% include OrderInformation_MemberSection %>

<% include OrderInformation_ShippingSection %>

<% if CustomerOrderNote %>
		<tr class="gap mainHeader">
			<th colspan="4" class="left" scope="col"><h2><% _t("CUSTOMERORDERNOTE","Customer Note") %></h2></th>
		</tr>
		<tr class="summary odd first">
			<td colspan="4" class="left fourRolDetail">$CustomerOrderNotee</td>
		</tr>
<% end_if %>
	</tbody>
</table>
</div>

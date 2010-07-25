<% if CustomerOrderNote %>
<div id="CustomerOrderNote">
	<h2 id="CustomerOrderNoteHeading">Nota Bene</h2>
	$CustomerOrderNote
</div>
<% end_if %>
<table id="InformationTable">
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
		<tr class="itemRow">
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

		<tr class="gap summary">
			<th colspan="3" scope="row" class="threeColHeader"><% _t("SUBTOTAL","Sub-total") %></th>
			<td class="right">$SubTotal.Nice</td>
		</tr>

		<% control Modifiers %>
			<% if ShowInTable %>
		<tr class="modifierRow">
			<td colspan="3" scope="row">$TableTitle</td>
			<td class="right">$TableValue</td>
		</tr>
			<% end_if %>
		<% end_control %>

		<tr class="gap Total">
			<th colspan="3" scope="row" class="threeColHeader"><% _t("TOTAL","Total") %></th>
			<td class="right">$Total.Nice $Currency</td>
		</tr>

<% include OrderInformation_PaymentSection %>

		<tr class="gap Total Outstanding">
			<th colspan="3" scope="row" class="threeColHeader"><strong><% _t("TOTALOUTSTANDING","Total outstanding") %></strong></th>
			<td class="right"><strong>$TotalOutstanding.Nice </strong></td>
		</tr>

<% include OrderInformation_MemberSection %>

<% include OrderInformation_ShippingSection %>

	</tbody>
</table>

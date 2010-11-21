<table id="InformationTable" class="infotable">
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
			<td colspan="3" scope="row" class="threeColHeader subtotal"><% _t("SUBTOTAL","Sub-total") %></td>
			<td class="right">$SubTotal.Nice</td>
		</tr>

		<% control Modifiers %>
			<% if ShowInTable %>
		<tr class="modifierRow $EvenOdd $FirstLast $Classes">
			<td colspan="3" scope="row">$TableTitle</td>
			<td class="right">$TableValue.Nice</td>
		</tr>
			<% end_if %>
		<% end_control %>

		<tr class="gap summary total" id="Total">
			<td colspan="3" scope="row" class="threeColHeader total"><% _t("TOTAL","Total") %></td>
			<td class="right">$Total.Nice $Currency</td>
		</tr>
	</tbody>
</table>
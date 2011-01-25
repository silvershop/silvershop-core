<table id="InformationTable" class="infotable">
	<thead>
		<tr>
			<th scope="col" class="left"><% _t("PRODUCT","Product") %></th>
			<th scope="col" class="center"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col" class="right"><% _t("PRICE","Price") %></th>
			<th scope="col" class="right"><% _t("TOTALPRICE","Total Price") %></th>
		</tr>
	</thead>
	<tfoot>
		<tr class="gap summary total" id="Total">
			<th colspan="3" scope="row" class="threeColHeader total"><strong><% _t("Order_Content.ss.TOTAL","Total") %></strong></th>
			<td class="right"><strong>$Total.Nice</strong></td>
		</tr>
	</tfoot>
	<tbody>
		<% control Items %>
		<tr  class="itemRow $EvenOdd $FirstLast">
			<td class="product title" scope="row">
				<% if Link %>
					<a href="$Link" title="<% sprintf(_t("Order_Content.ss.READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">$TableTitle</a> $TableSubTitle
				<% else %>
					$TableTitle $TableSubTitle
				<% end_if %>
			</td>
			<td class="center quantity">$Quantity</td>
			<td class="right unitprice">$UnitPrice.Nice</td>
			<td class="right total">$Total.Nice</td>
		</tr>
		<% end_control %>

		<tr class="gap summary" id="SubTotal">
			<th colspan="3" scope="row" class="threeColHeader subtotal"><% _t("Order_Content.ss.SUBTOTAL","Sub-total") %></th>
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

	</tbody>
</table>

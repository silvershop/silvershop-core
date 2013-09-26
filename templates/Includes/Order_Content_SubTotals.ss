<tfoot>
	<tr class="gap summary" id="SubTotal">
		<td colspan="5" scope="row" class="threeColHeader subtotal"><% _t("SUBTOTAL","Sub-total") %></td>
		<td class="right">$SubTotal.Nice</td>
	</tr>
	<% loop Modifiers %>
		<% if ShowInTable %>
	<tr class="modifierRow $EvenOdd $FirstLast $Classes">
		<td colspan="5" scope="row">$TableTitle</td>
		<td class="right">$TableValue.Nice</td>
	</tr>
		<% end_if %>
	<% end_loop %>
	<tr class="gap summary total" id="Total">
		<td colspan="5" scope="row" class="threeColHeader total"><% _t("TOTAL","Total") %></td>
		<td class="right">$Total.Nice $Currency</td>
	</tr>
</tfoot>

<tfoot>
	<tr class="gap summary" id="SubTotal">
		<td colspan="4" scope="row" class="threeColHeader subtotal"><% _t("SUBTOTAL","Sub-total") %></td>
		<td class="right">$SubTotal.Nice</td>
	</tr>
	<% control Modifiers %>
		<% if ShowInTable %>
	<tr class="modifierRow $EvenOdd $FirstLast $Classes">
		<td colspan="4" scope="row">$TableTitle</td>
		<td class="right">$TableValue.Nice</td>
	</tr>
		<% end_if %>
	<% end_control %>
	<tr class="gap summary total" id="Total">
		<td colspan="4" scope="row" class="threeColHeader total"><% _t("TOTAL","Total") %></td>
		<td class="right">$Total.Nice $Currency</td>
	</tr>
</tfoot>
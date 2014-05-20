<tfoot>
	<tr class="ss-gridfield-item">
		<th colspan="4" class="main"><% _t("SUBTOTAL","Sub-total") %></th>
		<th class="main">$SubTotal.Nice</th>
	</tr>
	<% loop Modifiers %>
		<% if ShowInTable %>
			<tr class="ss-gridfield-item ss-gridfield-$EvenOdd $FirstLast $Classes">
				<td colspan="4" class="main">
					$TableTitle
					<% if SubTitle %><small class="subtitle">($SubTitle)</small><% end_if %>
				</td>
				<td>$TableValue.Nice</td>
			</tr>
		<% end_if %>
	<% end_loop %>
	<tr class="ss-gridfield-item">
		<th colspan="4" class="main"><% _t("TOTAL","Total") %></th>
		<th class="main">$Total.Nice $Currency</th>
	</tr>
	<tr>
		<td class="bottom-all" colspan="5"></td>
	</tr>
</tfoot>

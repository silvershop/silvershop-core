<% require themedCSS(order) %>
<div id="OrderInformation">
	<% include Order_Shipping %>
	<% include Order_Content %>
	<% if Total %>
		<% if Payments %>
			<% include Order_Payments %>
		<% end_if %>
		<table id="OutstandingTable" class="infotable">
			<tbody>
				<tr class="gap summary" id="Outstanding">
					<th colspan="4" scope="row" class="threeColHeader"><strong><% _t("TOTALOUTSTANDING","Total outstanding") %></strong></th>
					<td class="right"><strong>$TotalOutstanding.Nice </strong></td>
				</tr>
			</tbody>
		</table>
	<% end_if %>
	<% if Notes %>
		<table id="NotesTable" class="infotable">
			<thead>
				<tr>
					<th><% _t("ORDERNOTES","Notes") %></th>
				</tr>
			</thead>
			</tbody>
				<tr>
					<td>$Notes</td>
				</tr>
			</tbody>
		</table>
	<% end_if %>
</div>
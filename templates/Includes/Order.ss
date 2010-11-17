<div id="OrderInformation">

	<% include Order_Shipping %>

	<% include Order_Content %>
	
	<% if Payments %>
		<% include Order_Payments %>
		
		<table id="OutstandingTable" class="infotable">
			<tbody>
				<tr class="gap summary" id="Outstanding">
					<th colspan="3" scope="row" class="threeColHeader"><strong><% _t("TOTALOUTSTANDING","Total outstanding") %></strong></th>
					<td class="right"><strong>$TotalOutstanding.Nice </strong></td>
				</tr>
			</tbody>
		</table>
	<% end_if %>


	<% if CustomerOrderNote %>
	<table id="NotesTable" class="infotable">
		<thead>
			<tr class="gap mainHeader">
				<th colspan="4" class="left" scope="col"><% _t("CUSTOMERORDERNOTE","Customer Note") %></th>
			</tr>
		</thead>
		</tbody>
			<tr class="summary odd first">
				<td colspan="4" class="left fourRolDetail">$CustomerOrderNote</td>
			</tr>
		</tbody>
	</table>
	<% end_if %>
	
</div>

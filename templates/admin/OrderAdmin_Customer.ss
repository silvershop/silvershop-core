<table class="order-customer ss-gridfield-table">
	<thead>
		<tr class="title">
			<th colspan="2">
				<h2><% _t("CUSTOMER","Customer") %></h2>
			</th>
		</tr>
		<tr class="header">
			<th class="main">Name</th>
			<th class="main">Email</th>
		</tr>
	</thead>
	<tbody>
		<tr class="ss-gridfield-item">
			<td>$Name</td>
			<td>
				<% if LatestEmail %>
					<a href="mailto:$LatestEmail">$LatestEmail</a>
				<% end_if %>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td class="bottom-all" colspan="5"></td>
		</tr>
	</tfoot>
</table>
<table class="table table-bordered orderhistory">
	<thead>
		<tr>
			<th>Reference</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th>
		</tr>
	</thead>
	<tbody>
		<% control PastOrders %>
			<tr class="$Status">
				<td>$Reference</td><td>$Created.Nice</td><td>$Items.Quantity</td><td>$Total.Nice</td><td>$Status</td><td><a class="btn btn-mini btn-primary" href="$Link"><i class="icon icon-white icon-eye-open"></i> view</a></td>
			</tr>
		<% end_control %>
	</tbody>
</table>
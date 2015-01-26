<div class="shop-dashboard dashboard-recent-orders">
	<table class="table table-bordered">
		<thead>
			<tr>
				<td>Date</td>
				<th>Email</th>
				<th>Name</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<% loop $Members %>
				<tr>
					<td>$Created.Nice</td>
					<td>$Email</td>
					<td>$Surname, $FirstName</td>
					<td>
						<a class="ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" href="admin/security/EditForm/field/Members/item/$ID/edit">Edit</a>
					</td>
				</tr>
			<% end_loop %>
		</tbody>
	</table>
</div>

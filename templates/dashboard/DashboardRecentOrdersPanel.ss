<% if $PanelSize == 'large' %>
	<div class="shop-dashboard dashboard-recent-orders">
		<table class="table table-bordered orderhistory">
			<thead>
				<tr>
					<th><%t Order.db_Reference "Reference" %></th>
					<th><%t ShopDashboard.DATE "Date" %></th>
					<th><%t ShopDashboard.CUSTOMER "Customer" %></th>
					<th><%t AccountNavigation.EMAIL "Email" %></th>
					<th><%t Order.has_many_Items "Items" %></th>
					<th><%t Order.db_Total "Total" %></th>
					<th><%t Order.db_Status "Status" %></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<% loop $Orders %>
					<tr class="$Status">
						<td>$Reference</td>
						<td>$Placed.Nice</td>
						<td>$Surname, $FirstName</td>
						<td>$Email</td>
						<td>$Items.Quantity</td>
						<td>$Total.Nice</td>
						<td>$Status</td>
						<td>
							<a class="ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" href="admin/orders/Order/EditForm/field/Order/item/$ID/edit">
								<%t ShopDashboard.EDIT "Edit" %>
							</a>
						</td>
					</tr>
				<% end_loop %>
			</tbody>
		</table>
	</div>
<% else %>
	<div class="shop-dashboard dashboard-recent-orders">
		<table class="table table-bordered orderhistory">
			<thead>
				<tr>
          <th><%t Order.db_Reference "Reference" %></th>
          <th><%t ShopDashboard.CUSTOMER "Customer" %></th>
          <th><%t Order.db_Total "Total" %></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<% loop $Orders %>
					<tr class="$Status">
						<td>$Reference</td>
						<td>$Surname, $FirstName</td>
						<td>$Total.Nice</td>
						<td>
							<a class="ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" href="admin/orders/Order/EditForm/field/Order/item/$ID/edit">
								<%t ShopDashboard.EDIT "Edit" %>
							</a>
						</td>
					</tr>
				<% end_loop %>
			</tbody>
		</table>
	</div>
<% end_if %>

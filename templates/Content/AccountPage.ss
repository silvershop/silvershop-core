<div class="typography">
	$Content
	<h2 class="pagetitle">Past Orders</h2>
	<% with Member %>
		<% if PastOrders %>
			<% include OrderHistory %>
		<% else %>
			<p class="message warning">No past orders found.</p>
		<% end_if %>
	<% end_with %>
</div>
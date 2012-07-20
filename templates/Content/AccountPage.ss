<div class="typography">
	$Content
	<h2 class="pagetitle">Past Orders</h2>
	<% control Member %>
		<% if PastOrders %>
			<% include OrderHistory %>
		<% else %>
			<p class="message warning">No past orders found.</p>
		<% end_if %>
	<% end_if %>
</div>
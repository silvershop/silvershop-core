<% require themedCSS(account) %>
<div class="accountnav">
	<% include AccountPageSideBar %>
</div>
<div class="typography accountcontent orderhistory">
	<h2 class="pagetitle">Past Orders</h2>
	<% with CurrentMember %>
		<% if PastOrders %>
			<% include OrderHistory %>
		<% else %>
			<p class="message warning">No past orders found.</p>
		<% end_if %>
	<% end_with %>
</div>
<div class="clear"></div>
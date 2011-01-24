<div id="OrderInformation">

	<h2 class="blackHeading">$Title</h2>

	<% include Order_Addresses %>

	<% include Order_Content %>

	<% if Payments %><% include Order_Payments %><% end_if %>

	<% include Order_OutstandingTotal %>

	<% include Order_CustomerNote %>

</div>

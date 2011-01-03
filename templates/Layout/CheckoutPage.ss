<div id="Checkout">
	<h3 class="process"><span><% _t("PROCESS","Process") %>:</span> &nbsp;<span class="current"><% _t("CHECKOUT","Checkout") %></span> &nbsp;&gt;&nbsp;<% _t("ORDERSTATUS","Order Status") %></h3>

	<div class="typography">
		<h2 class="pageTitle">$Title</h2>
		<% if Content %>
			$Content
		<% end_if %>
	</div>

	<% if CanCheckout %>
		<% control Order %>
			<% include Order_Content_Editable %>
		<% end_control %>

		<% control ModifierForms %>
			$Me
		<% end_control %>

		<% if Order.Items %>$OrderForm<% end_if %>
	<% else %>
	<div id="CanNotCheckOut">
		<p><strong>$Message</strong></p>
		<% if UsefulLinks %>
		<ul id="UsefulLinks">
			<% control UsefulLinks %><li><a href="$Link">$Title</a></li><% end_control %>
		</ul>
		<% end_if %>
	</div>
	<% end_if %>
</div>

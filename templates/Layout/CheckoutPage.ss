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
		<p><strong>$Message</strong></p>
	<% end_if %>
</div>

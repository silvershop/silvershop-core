<% require themedCSS(checkout) %>
<div id="Checkout">
	<div class="typography">
		<h2 class="pageTitle">$Title</h2>
		<% if Content %>
			$Content
		<% end_if %>
	</div>
	
	<% if CanCheckout %>
		<% control Cart %>
			<% include Order_Content_Editable %>
		<% end_control %>
		
		<% control ModifierForms %>
			$Me
		<% end_control %>
		
		<% if Cart.Items %>$OrderForm<% end_if %>
	<% else %>
		<p><strong>$Message</strong></p>
	<% end_if %>
</div>

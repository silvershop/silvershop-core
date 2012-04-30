<% require themedCSS(checkout) %>
<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
	<div class="typography">
		<% if Content %>
			$Content
		<% end_if %>
	</div>
	<% if Cart %>
		<% control Cart %>
			<% include Order_Content_Editable %>
		<% end_control %>
		<% control ModifierForms %>
			$Me
		<% end_control %>
		<% if Cart.Items %>$OrderForm<% end_if %>
	<% else %>
		<p class="message warning"><% _t('CheckoutPage.ss.CARTEMPTY','Your cart is empty.') %></p>
	<% end_if %>
</div>

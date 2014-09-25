<% require themedCSS(checkout) %>
<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
	<div class="typography">
		
		<% if PaymentErrorMessage %>
		    <p class="message error">
		    <% _t('CheckoutPage.PaymentErrorMessage', 'Received error from payment gateway:') %>
		    $PaymentErrorMessage
		    </p>
		<% end_if %>

		<% if Content %>
			$Content
		<% end_if %>
	</div>
	<% if Cart %>
		<% with Cart %>
			<% include Cart ShowSubtotals=true %>
		<% end_with %>
		$OrderForm
	<% else %>
		<p class="message warning"><% _t('CheckoutPage.ss.CARTEMPTY','Your cart is empty.') %></p>
	<% end_if %>
</div>

<% require ThemedCSS(checkout) %>
<h1 class="pagetitle">$Title</h1>
<div class="typography">
	<% if Content %>
		$Content
	<% end_if %>
</div>
<% if Cart %>
	
	<% if CartForm %>
		$CartForm
	<% else %>
		<% with Cart %><% include Cart Editable=true %><% end_with %>
	<% end_if %>
	
<% else %>
	<p class="message warning"><% _t('CartPage.ss.CARTEMPTY','Your cart is empty.') %></p>
<% end_if %>
<div class="cartfooter">
	<% if ContinueLink %>
		<a class="continuelink button" href="$ContinueLink">
			<% _t('CartPage.ss.CONTINUE','Continue Shopping') %>
		</a>
	<% end_if %>
	<% if Cart %>
		<% if CheckoutLink %>
			<a class="checkoutlink button" href="$CheckoutLink">
				<% _t('CartPage.ss.PROCEEDTOCHECKOUT','Proceed to Checkout') %>
			</a>
		<% end_if %>
	<% end_if %>
</div>
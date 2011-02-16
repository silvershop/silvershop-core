<h1 class="pagetitle">$Title</h1>
<div id="ContentHolder">
	<% if Content %>
		$Content
	<% end_if %>
</div>
<div id="OrderHolder">
<% control Order %>
	<% include Order_Content_Editable %>
<% end_control %>
</div>
<% if ContinuePage %><a class="continuelink button" href="$ContinuePage.Link"><% _t('Cart.CONTINUESHOPPING','continue shopping') %></a><% end_if %>
<% if CheckoutPage %><a class="checkoutlink button" href="$CheckoutPage.Link"><% _t('Cart.CHECKOUTGOTO','proceed to checkout') %></a><% end_if %>

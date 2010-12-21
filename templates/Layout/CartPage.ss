<h1 class="pagetitle">$Title</h1>
<div class="typography">
	<% if Content %>
		$Content
	<% end_if %>
</div>
<% control Order %>
	<% include Order_Content_Editable %>
<% end_control %>
<% if ContinuePage %><a class="continuelink button" href="$ContinuePage.Link">continue shopping</a><% end_if %>
<% if CheckoutPage %><a class="checkoutlink button" href="$CheckoutPage.Link">proceed to checkout</a><% end_if %>
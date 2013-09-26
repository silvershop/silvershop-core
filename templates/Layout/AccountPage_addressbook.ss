<% require themedCSS(account) %>
<% include AccountNavigation %>
<div class="typography">
	<h2>Default Addresses</h2>
	<% if DefaultAddressForm %>
		$DefaultAddressForm
	<% else %>
		<p class="alert">No addresses found.</p>
	<% end_if %>
	<h2>Create New Address</h2>
	$CreateAddressForm
</div>
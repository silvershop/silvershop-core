<div class="row">
	<div class="span5">
		<h2>Default Addresses</h2>
		<% if DefaultAddressForm %>
			$DefaultAddressForm
		<% else %>
			<p class="alert">No addresses found.</p>
		<% end_if %>
	</div>
	<div class="span4">
		<h2>Create New Address</h2>
		<div class="well">
			$CreateAddressForm
		</div>
	</div>
</div>
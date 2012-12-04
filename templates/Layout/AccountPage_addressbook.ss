<% require themedCSS(account) %>
<div class="accountnav">
	<% include AccountPageSideBar %>
</div>
<div class="typography accountcontent addressbook">
	<h2>Default Addresses</h2>
	<% if DefaultAddressForm %>
		$DefaultAddressForm
	<% else %>
		<p class="alert">No addresses found.</p>
	<% end_if %>
	<h2>Create New Address</h2>
	<div class="well">
		$CreateAddressForm
	</div>
</div>
<div class="clear"></div>
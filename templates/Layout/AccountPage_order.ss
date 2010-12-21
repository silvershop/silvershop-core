<div id="Account">
	<div class="typography">
	<% if Order %>
		<% control Order %>
			<h2><% _t('AccountPage.ss.ORDER','Order') %> #$ID</h2>
			<h3>$Created.Nice</h3>
			<% include Order %>
		<% end_control %>
	<% else %>
		<div id="AccountMessage">$Message.Raw</div>
	<% end_if %>
	$Form
	</div>
</div>

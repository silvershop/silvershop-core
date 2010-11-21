<div id="Account">
	<div class="typography">
	<% if Order %>
		<% control Order %>
			<h2><% _t('AccountPage.ss.ORDER','Order') %> #$ID ($Created.Long)</h2>

			<% include Order %>
		<% end_control %>
	<% else %>
		<div id="AccountMessage">$Message.Raw</div>
	<% end_if %>
	</div>
</div>

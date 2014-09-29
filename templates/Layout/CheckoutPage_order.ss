<div id="Account">
	<div class="typography">
		<% if Order %>
			<% with Order %>
				<h2><% _t('AccountPage.ss.ORDER','Order') %> $ID ($Created.Long)</h2>
			<% end_with %>
		<% end_if %>
		<% if Message %>
			<p class="message $MessageType">$Message</p>
		<% end_if %>
		<% if Order %>
			<% with Order %>				
				<% include Order %>
			<% end_with %>
			$Form
		<% end_if %>
	</div>
</div>
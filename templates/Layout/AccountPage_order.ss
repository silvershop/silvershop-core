<div id="Account">
	<div class="typography">
		<% if Order %>
			<% control Order %>
				<h2><% _t('AccountPage.ss.ORDER','Order') %> #$ID ($Created.Long)</h2>
			<% end_control %>
		<% end_if %>	
		
		<% if Message %>
			<p class="message $MessageType">$Message</p>
		<% end_if %>
	
		<% if Order %>
			<% control Order %>				
				<% include Order %>
			<% end_control %>
		<% end_if %>
	</div>
</div>
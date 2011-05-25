<div id="Account">
	<div class="typography">
	
	$OrderFinishMessage
	
	<% if Order %>
		<% control Order %>
			<h2><% _t('AccountPage.ss.ORDER','Order') %> #$ID ($Created.Long)</h2>
			
			<% include Order %>
		<% end_control %>
	<% else %>
		<p class="message $MessageType">$Message</p>
	<% end_if %>
	</div>
</div>
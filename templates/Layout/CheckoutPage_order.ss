<div id="Account">
	<div class="typography">
	
		<% if SuccessMessage %>
			$SuccessMessage
		<% end_if %>
		
		<% if Order %>
			<% control Order %>
				<h2><% _t('AccountPage.ss.ORDER','Order') %> $ID ($Created.Long)</h2>
			<% end_control %>
		<% end_if %>
		
		<% if Order %>
			<% control Order %>				
				<% include Order %>
			<% end_control %>
			$PaymentForm
		<% end_if %>
		
	</div>
</div>
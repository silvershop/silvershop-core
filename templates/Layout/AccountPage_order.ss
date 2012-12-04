<% require themedCSS(account) %>
<div class="accountnav">
	<% include AccountPageSideBar %>
</div>
<div class="typography accountcontent order">

	$SuccessMessage
	
	<% if Order %>
		<% control Order %>
			<h2><% _t('AccountPage.ss.ORDER','Order') %> $Reference ($Created.Long)</h2>
		<% end_control %>
	<% end_if %>
	
	<% if Order %>
		<% control Order %>				
			<% include Order %>
		<% end_control %>
		$PaymentForm
	<% end_if %>
</div>
<div class="clear"></div>
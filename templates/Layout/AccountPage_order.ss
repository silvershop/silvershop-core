<% require css("silvershop/css/account.css") %>
<% require themedCSS("shop") %>
<% require themedCSS("account") %>
<% include AccountNavigation %>
<div class="typography">
	<% if $Order %>
		<% with $Order %>
			<h2><% _t('AccountPage.ss.ORDER','Order') %> $Reference ($Created.Long)</h2>
		<% end_with %>
	<% end_if %>
	<% if $Message %>
		<p class="message $MessageType">$Message</p>
	<% end_if %>
	<% if $Order %>
		<% with $Order %>
			<% include Order %>
		<% end_with %>
		$ActionsForm
	<% end_if %>
</div>

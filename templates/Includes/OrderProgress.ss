<div class="block">
	<h3>Status</h3>
	<div class="status<% if Validate %> validate<% end_if %>">
		<p>Payment</p>
	</div>
	<div class="status<% if Validate %> validate<% if Processing %> processing<% end_if %><% end_if %>"><p>Validation</p></div>
	<div class="status<% if Processing %> processing<% if Sent %> sent<% end_if %><% end_if %>"><p>Processing</p></div>
	<div class="status<% if Sent %> sent<% end_if %>"><p>Sending</p></div>
	<div class="clear"><!-- --></div>
</div>
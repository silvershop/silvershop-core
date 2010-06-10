<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
	<head>
		<% base_tag %>
		<title>Invoice</title>
		</head>
		<body>
			<div id="Invoice">
				<% control DisplayFinalisedOrder %>
					<h1>Invoice for Order #$ID</h1>
					<% include OrderInformation %>
				<% end_control %>
			</div>
		</body>
</html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<% base_tag %>
	<title><% _t("PAGETITLE","Print Orders") %></title>
</head>
<body>
	<a href="#" onclick="window.print(); return false;" id="PrintPageIcon">print now</a>
	<div class="typography">
		<div id="OrderInformation">
			<% control DisplayFinalisedOrder %>
				<% include OrderInformation_Print_Details %>
			<% end_control %>
		</div>
	</div>
</body>
</html>

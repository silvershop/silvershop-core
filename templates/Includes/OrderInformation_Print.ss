<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<% base_tag %>
	<title><% _t("PAGETITLE","Print Orders") %></title>
</head>
<body>
	<div id="Icons">
		<a href="#" onclick="window.print(); return false;" id="PrintPageIcon">print now</a>
		<a href="#" onclick="window.close(); return false;" id="PrintCloseIcon">close</a>
	</div>
<% control DisplayFinalisedOrder %><% include OrderInformation_Print_Details %><% end_control %>
	<div id="OrderStatus">
		<div id="OrderStatusForm">
			$StatusForm
		</div>
		<div id="OrderStatusLog">
			$StatusLog
		</div>
	</div>
</body>
</html>


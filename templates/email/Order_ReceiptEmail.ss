<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
		<title><% _t("TITLE","Shop Receipt") %></title>
		<% include OrderReceiptStyle %>
	</head>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0" summary="Email Information">
			<thead>
				<tr>
					<th scope="col" colspan="2">
						<h1 class="emailTitle"><% _t("HEADLINE","Shop Order Receipt") %></h1>
					</th>
				</tr>
				<tr>
					<td scope="col">
						<h1 class="PageTitle">$Subject</h1>
					</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						$PurchaseCompleteMessage
					</td>
				</tr>
				<% if Order %>
				<% control Order %>
					<tr>
						<td>
							<% include OrderInformation %>
						</td>
					</tr>
				<% end_control %>
				<% end_if %>
			</tbody>
		</table>
	</body>
</html>

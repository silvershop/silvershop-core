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
						<h1 class="title">$Subject</h1>
					</th>
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
							<% include Order %>
						</td>
					</tr>
				<% end_control %>
				<% end_if %>
			</tbody>
		</table>
	</body>
</html>

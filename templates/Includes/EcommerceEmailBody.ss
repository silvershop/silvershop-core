<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
		<title>$Subject</title>
		<% include EmailReceiptStyle %>
	</head>
	<body>
		<% include EmailReceiptStyle %>
		<table id="Content" cellspacing="0" cellpadding="0" summary="Email Information">
			<thead>
				<tr>
					<th>
						<h1 class="title">$Subject</h1>
					</th>
				</tr>
			</thead>
			<% if Message %><tfoot>
				<tr>
					<th>
						$Message
					</th>
				</tr>
			</tfoot><% end_if %>
			<tbody>
				<% if Order %>
				<tr>
					<td>
						<% control Order %><% include Order %><% end_control %>
					</td>
				</tr>
				<% end_if %>
			</tbody>
		</table>
	</body>
</html>

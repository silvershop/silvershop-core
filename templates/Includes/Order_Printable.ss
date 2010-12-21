<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<% base_tag %>
	<title><% _t("PAGETITLE","Print Orders") %></title>
	<% include OrderReceiptStyle %>
</head>
<body>
	
	<%-- todo: allow printing multiple invoices at once --%>
	<div style="page-break-after: always;">
		<h1 class="title">$Top.SiteConfig.Title Invoice</h1>
		
		<table id="SenderTable">
			<tbody>
				<tr>
					<td class="sender">
						$Top.SiteConfig.SenderAddress
					</td>
					<td class="meta">
						<table id="MetaTable">
							<tbody>
								<tr><td class="label">Invoice Date:</td><td class="date">$Now.Nice</td></tr>
								<tr><td class="label">Order ID:</td><td class="id">$DisplayFinalisedOrder.ID</td></tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		$Content
		<% control DisplayFinalisedOrder %>
			<% include Order %>
		<% end_control %>
	</div>
	
</body>
</html>


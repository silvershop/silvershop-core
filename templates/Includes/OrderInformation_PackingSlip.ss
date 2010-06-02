<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
	<head>
		<% base_tag %>
		<link xmlns="http://www.w3.org/1999/xhtml" rel="stylesheet" type="text/css" href="ecommerce/css/Order_PackingSlip.css" />
		<style type="text/css">
			html,body{
				text-align:left;
				border:0;
				padding:0;
				margin:0;
				background:#e9e9e9;
			}
			body {
				font-size:62.5%;
			}
			
			p.footertext {
				text-align:center;
				color:#666;
				font-size:10px;
			}
			
			* {
				font-size:1em;
			}
			h2.pageTitle {
				font-size:2em;
			}
			#OrderInformation div,
			#OrderStatusForm,
			#OrderStatusLog {
				margin:20px;
				background:#fff;
				padding:20px;
				border:1px solid #333;
				page-break-after:always;
				font-family:Arial,Helvetica,sans-serif;
				width:600px;
				text-align:left;
			}
			@media=print( 
				#OrderStatusForm,
				#OrderStatusLog {
					display: none;
				}
			)
		</style>
		<title><% _t("PAGETITLE","Shop Print Orders") %></title>
		</head>
		<body>
			<div id="OrderInformation">
				<% control DisplayFinalisedOrder %>
				<div>
					<table class="packingSlip" cellspacing="0" cellpadding="0" summary="<% _t("TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
						
						<thead>
							<tr class="gap">
								<td colspan="4" scope="row" class="left ordersummary"><!-- insert header here --></td>
								<td><h2>Packing Slip</h2></td>
							</tr>
						</thead>
						
						<tbody>
							<tr class="gap">
								<td colspan="5" scope="row" align="right">$Now.Long</th>
							</tr>
							<tr class="gap">
								<td colspan="3"></td>
								<td valign="top">Ship to:</td>
								<td>
									<% if ShippingName %>
										$ShippingName<br />
										<% if ShippingAddress %>
											$ShippingAddress<br />
										<% end_if %>
										<% if ShippingAddress2 %>
											$ShippingAddress2<br />
										<% end_if %>
										<% if ShippingCity %>
											$ShippingCity<br />
										<% end_if %>
										<% if ShippingCountry %>
											$ShippingCountry<br />
										<% end_if %>
									<% else %>
										<% control Customer %>
											$CreditCardName<br />
											$Address<br />
											$AddressLine2<br />
											$City<br />
											$Country<br />
										<% end_control %>
									<% end_if %>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="packingSlip orderDetails" cellspacing="0" cellpadding="0">
						<tbody>
							<tr class="orderDetails">
								<td colspan="3"><% _t("ORDERDATE","Order Date") %></td>
								<td><% _t("ORDERNUMBER","Order Number") %></td>
							</tr>
							<tr>
								<td colspan="3">$Created.Nice</td>
								<td>$ID</td>
							</tr>
						</tbody>
					</table>
					<table class="packingSlip orderDetails" cellspacing="0" cellpadding="0">
						<tbody>
							<tr class="orderDetails">
								<td colspan="3"><% _t("ITEM","Item") %> #</td>
								<td><% _t("DESCRIPTION","Description") %></td>
								<td><% _t("QUANTITY","Quantity") %></td>
							</tr>
							<% control Items %>
								<tr>
									<td colspan="3">$InternalItemID</td>
									<td>$Title</td>
									<td>$Quantity</td>
								</tr>
							<% end_control %>
						</tbody>
					</table>
				</div>
				<% end_control %>
			</div>
		</body>
</html>

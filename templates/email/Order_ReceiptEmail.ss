<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
		<title><% _t("TITLE","Shop Receipt") %></title>
		<style>
			<!--
			/** Global resetting for Design **/
				html {
					font-size:1em;
					font-family:Verdana, Arial, sans-serif;
				}
				body {
					font-size:62.5%;
					padding:0;
					margin:0;
				}
				a img { border:0; }
				#Content {
					text-align:left;
					margin:auto;
					padding-left:20px;
				}
				#Content td {
				}
				#Content .emailTitle {
					color:#4EA3D7;
					font-family: Tahoma, Verdana, sans-serif;
					font-weight: normal;
					font-size: 20px;
				}
				#Content .PageTitle {
					padding:5px;
					color: #333;
					font-size:14px;
					font-family: Tahoma, Verdana, sans-serif;
				}
				#Content .footer td {
					padding:10px;
				}
				#Content .footer td.right{ text-align:right;}
				#Content .typography { padding:0px 10px; }
				#Content .typography a {
					font-size:1em;
					text-decoration:underline;
				}
					#Content .typography a:hover {
						text-decoration:none;
					}	
				#Content .typography ul { padding:2px 15px;}
				#Content .typography ul li { padding:2px 5px;}
				#Content .typography p {
					margin:0.75em 0em;
					font-size:12px;
					color: #666;
				}
				#InformationTable {
					border-top:1px solid #ccc;
					border-bottom:1px solid #ccc;
					width:600px;
					background:#fff;
				}
					.warningMessage {
						margin: 4px 0 0 3px;
						padding: 5px;
						width: 92%;
						color: #DC1313;
						border: 4px solid #FF7373;
						background: #FED0D0;
					}		
					#InformationTable h3 {
						color: #4EA3D7;
						font-size: 15px;
						font-weight: normal;
						font-family: Tahoma, Verdana, sans-serif;
					}
					#InformationTable tr.Total {
						background: #c9ebff;
					}
					#InformationTable tr.Total td {
						font-weight:bold;
						font-size:14px;
						color:#4EA3D7;
						text-transform:uppercase;
					}
						#InformationTable tr td,
						#InformationTable tr th {
							padding:5px;
							color: #333;
						}
							#InformationTable td {
								font-size:12px;
							}
							#InformationTable tr.summary {
								font-weight: bold;
							}
							#InformationTable td.ordersummary {
								font-size:1em;
								border-bottom:1px solid #ccc;
							}
							#InformationTable th {
								font-weight:bold;
								font-size:14px;
								color:#4EA3D7 !important;
							}
							#InformationTable tr td a {
								color:#4EA3D7;
								text-decoration:underline;
							}
								#InformationTable tr td a:hover {
									text-decoration:none;
								}
					#InformationTable .right {
						text-align:right;
					}
					#InformationTable .center {
						text-align:center;
					}
					#InformationTable .left {
						text-align:left;
					}
			-->
		</style>
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
				<% control Order %>
					<tr>
						<td>
							<% include OrderInformation %>
						</td>
					</tr>
				<% end_control %>
			</tbody>
		</table>
	</body>
</html>

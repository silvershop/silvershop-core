<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
		<title><% _t("TITLE","Shop Status Change") %></title>
		<style>
			<!--
			/** Global resetting for Design **/
				html {
					font-size:1em;
					font-family:Arial,Helvetica,sans-serif;
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
				#Content h1.PageTitle {
					padding:5px;
					font-size:14px;
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
				}
#InformationTable {
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	width:600px;
	background:#fff;
}	
	#InformationTable tr.Total td {
		font-weight:bold;
		font-size:14px;
		color:#006e7f;
		text-transform:uppercase;
	}
		#InformationTable tr td,
		#InformationTable tr th {
			padding:5px;
		}
			#InformationTable td {
				font-size:12px;
			}
			#InformationTable td.ordersummary {
				font-size:1em;
				border-bottom:1px solid #ccc;
			}
			#InformationTable th {
				font-weight:bold;
				font-size:14px;
				color:#006e7f;
			}
			#InformationTable tr td a {
				color:#006e7f;
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
						<h1><% _t("HEADLINE","Shop Status Change") %></h1>
					</th>
				</tr>
				<tr>
					<td scope="col">
						<h1 class="PageTitle">$Subject</h1>
					</td>
				</tr>
			</thead>
			<tbody>
				<% if Order %>
				<% control Order %>
					<tr>
						<td scope="row" colspan="2" class="typography">	
							<p><% sprintf(_t("STATUSCHANGE","Status changed to \"%s\" for Order #"),$Status) %>{$ID}</p>
						</td>
					</tr>
				<% end_control %>
				<% end_if %>
				<tr>
					<td scope="row" colspan="2" class="typography">
						<p>$Note</p>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>

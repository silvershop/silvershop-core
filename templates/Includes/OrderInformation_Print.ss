<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
	<head>
		<% base_tag %>
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
			* {
				font-size:1em;
			}
			h2.pageTitle {
				font-size:2em;
			}
			#OrderInformation,
			#OrderStatus {
				margin:20px;
				background:#fff;
				padding:20px;
				border:1px solid #333;
				font-family:Verdana,Arial,Helvetica,sans-serif;
				width:600px;
			}
			
			#OrderStatus label.left {
				width:100px;
				float:left;
			}

		/* Information table styling */
		#InformationTable {
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			background: #fdfdfd;
		}
			
			#InformationTable tr.Total {
				background: #c9ebff;
			}
			
			/* apply the colour to these elements */
			#InformationTable tr.Total td,
			#InformationTable th {
				color: #4EA3D7 !important;
				font-weight: bold;
			}
				.warningMessage {
					margin: 4px 0 0 3px;
					padding: 5px;
					width: 92%;
					color: #DC1313;
					border: 4px solid #FF7373;
					background: #FED0D0;
				}
			
			/* total line in order information table */
			#InformationTable tr.Total td {
				text-transform: uppercase;
			}
			
			#InformationTable tr.summary {
				font-weight: bold;
			}
				#InformationTable tr td,
				#InformationTable tr th {
					padding: 5px;
					font-size: 1.2em;
					color: #333;
				}
					#InformationTable td.product {
						width: 30%;
					}
					#InformationTable td.ordersummary {
						font-size: 1em;
						border-bottom: 1px solid #ccc;
					}
					#InformationTable tr td a {
						color: #666;
					}
						#InformationTable tr td a img {
							vertical-align: middle;
						}
			
			/* Information table alignment classes */
			#InformationTable .right {
				text-align: right;
			}
			#InformationTable .center {
				text-align: center;
			}
			#InformationTable .left {
				text-align: left;
			}
			
		</style>
		<script type="text/javascript">
			if(document.location.href.indexOf('print=1') > 0) {
				window.print();
			}
		</script>
		<title><% _t("PAGETITLE","Print Orders") %></title>
		</head>
		<body>
				<div id="OrderInformation">
					<% control DisplayFinalisedOrder %>
						<% include OrderInformation %>
					<% end_control %>
				</div>
				<div id="OrderStatus">
					$StatusForm
					$StatusLog
				</div>
		</body>
</html>

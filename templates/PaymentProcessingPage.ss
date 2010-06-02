<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<% base_tag %>
		$MetaTags
		<link rel="shortcut icon" href="/favicon.ico"/>
		
		<!--[if IE 6]>
			<style type="text/css">
			 @import url(themes/blackcandy/css/ie6.css);
			</style> 
		<![endif]-->
		
		<!--[if IE 7]>
			<style type="text/css">
			 @import url(themes/blackcandy/css/ie7.css);
			</style> 
		<![endif]-->
		<style type="text/css">
			html, body {
				width: 100%;
				height: 100%;
				margin: 0 auto;
				background-color: #ffffff;
			}
			body {
				display: table;
				overflow: hidden;
				margin-left: auto;
				margin-right: auto;
				text-align: center;
			}
			#Outer {
				position: relative;
				top: 50%;
				display: table-cell;
				vertical-align: middle;
			}
			#Inner {
				position: relative;
				top: -50%;
			}
			#Inner img {
				margin: 10px 0;
				border: 0;
			}
		</style>
	</head>
	<body>
		<div id="Outer">
			<div id="Inner">
				$Logo<br/>
				<img src="ecommerce/images/loading.gif" alt="Loading image">
				$Form
			</div>
		</div>
	</body>
</html>
<style>
	/** Global Resets for Email Design **/
	/** Reference: https://github.com/seanpowell/Email-Boilerplate/blob/master/email.html **/
	
	html {
		font-size: 1em;
		font-family: Tahoma, Verdana, sans-serif;
	}
	
	body, table#container {
		font-size: 12px;
		line-height: 100% !important;
		padding: 0;
		margin: 0;
		width: 100% !important;
		height: 100%;
		box-sizing: border-box;
		-webkit-font-smoothing: antialiased;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust:100%;
	}
	
	table td {border-collapse: collapse;}  /* Outlook 07 & 10 padding issue */

	table {     /* Remove spacing around Outlook 07, 10 tables */
		border-collapse:collapse;
		mso-table-lspace:0pt;
		mso-table-rspace:0pt;
	}

	img {
		outline:none;
		text-decoration:none; 
		-ms-interpolation-mode: bicubic;
		display:block;
	}

	a img { border: none; }
	
	table#container {
		margin: auto;
	}

	/* Main left and right padding */
	table#container > tr > td {
            padding: 0 20px 0 20px;
    }


</style>
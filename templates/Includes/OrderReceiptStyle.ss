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
        border:0;
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

    /* Styling for Silvershop order */
    h1.title{
        font-size:1.5em;
        display:block;
        text-align:right;
        border-bottom:1px solid #CDDDDD;
        text-transform:uppercase;
    }

    #Content {
        text-align:left;
        margin:auto;
    }

    table#SenderTable{
        width:100%;
    }
        table#SenderTable .sender,
        table#SenderTable .meta{
            width:50%;
        }

    table#MetaTable{
        margin-left:auto;
    }

    table#MetaTable .label{
        font-weight:bold;
    }

    table.infotable{
        border:1px solid #CDDDDD;
        border-collapse:collapse;
        width:100%;
        border-top:1px solid #ccc;
        border-bottom:1px solid #ccc;
        background:#fff;
        margin-top:10px;
    }
    .warningMessage {
        margin: 4px 0 0 3px;
        padding: 5px;
        width: 92%;
        color: #DC1313;
        border: 4px solid #FF7373;
        background: #FED0D0;
    }
    table.infotable h3 {
        color: #4EA3D7;
        font-size: 15px;
        font-weight: normal;
        font-family: Tahoma, Verdana, sans-serif;
    }

    table.infotable tr.Total td {
        font-weight:bold;
        font-size:14px;

        text-transform:uppercase;
    }
    table.infotable tr td,
    table.infotable tr th {
        padding:5px;
        color: #333;
        border:1px solid #CDDDDD;
    }
    table.infotable td {
        font-size:12px;
    }
    table.infotable tr.summary {
        font-weight: bold;
    }
    table.infotable td.ordersummary {
        font-size:1em;
        border-bottom:1px solid #ccc;
    }
    table.infotable th {
        font-weight:bold;
        font-size:12px;
        color:#000;
        background:#E7EFEF;
    }
    table.infotable tr td a {
        color:#4EA3D7;
        text-decoration:underline;
    }
        table.infotable tr td a:hover {
            text-decoration:none;
        }
    table.infotable .modifierRow,
    table.infotable .threeColHeader{
        text-align:right;
    }

    table.infotable .right {
        text-align:right;
    }
    table.infotable .center {
        text-align:center;
    }
    table.infotable .left,
    table.infotable th {
        text-align:left;
    }

    #ShippingTable td,
    #ShippingTable th{
        width:50%;
    }
</style>

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
    h1.silvershop-title{
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
        table#SenderTable .silvershop-sender,
        table#SenderTable .silvershop-meta{
            width:50%;
        }

    table#MetaTable{
        margin-left:auto;
    }

    table#MetaTable .silvershop-label{
        font-weight:bold;
    }

    table.silvershop-infotable{
        border:1px solid #CDDDDD;
        border-collapse:collapse;
        width:100%;
        border-top:1px solid #ccc;
        border-bottom:1px solid #ccc;
        background:#fff;
        margin-top:10px;
    }
    .silvershop-warningMessage {
        margin: 4px 0 0 3px;
        padding: 5px;
        width: 92%;
        color: #DC1313;
        border: 4px solid #FF7373;
        background: #FED0D0;
    }
    table.silvershop-infotable h3 {
        color: #4EA3D7;
        font-size: 15px;
        font-weight: normal;
        font-family: Tahoma, Verdana, sans-serif;
    }

    table.silvershop-infotable tr.silvershop-total td {
        font-weight:bold;
        font-size:14px;

        text-transform:uppercase;
    }
    table.silvershop-infotable tr td,
    table.silvershop-infotable tr th {
        padding:5px;
        color: #333;
        border:1px solid #CDDDDD;
    }
    table.silvershop-infotable td {
        font-size:12px;
    }
    table.silvershop-infotable tr.silvershop-summary {
        font-weight: bold;
    }
    table.silvershop-infotable td.silvershop-ordersummary {
        font-size:1em;
        border-bottom:1px solid #ccc;
    }
    table.silvershop-infotable th {
        font-weight:bold;
        font-size:12px;
        color:#000;
        background:#E7EFEF;
    }
    table.silvershop-infotable tr td a {
        color:#4EA3D7;
        text-decoration:underline;
    }
        table.silvershop-infotable tr td a:hover {
            text-decoration:none;
        }
    table.silvershop-infotable .silvershop-modifierRow,
    table.silvershop-infotable .silvershop-threeColHeader{
        text-align:right;
    }

    table.silvershop-infotable .silvershop-right {
        text-align:right;
    }
    table.silvershop-infotable .silvershop-center {
        text-align:center;
    }
    table.silvershop-infotable .silvershop-left,
    table.silvershop-infotable th {
        text-align:left;
    }

    #ShippingTable td,
    #ShippingTable th{
        width:50%;
    }
</style>

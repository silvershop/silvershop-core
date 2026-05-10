<style>
    /** Global Resets for Email Design **/
    /** Reference: https://github.com/seanpowell/Email-Boilerplate/blob/master/email.html **/

    html {
        font-size: 1em;
        font-family: Tahoma, Verdana, sans-serif;
    }

    body, table.silvershop-email {
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

    table.silvershop-email {
        margin: auto;
    }

    /* Main left and right padding */
    table.silvershop-email > tr > td {
            padding: 0 20px 0 20px;
    }

    /* Styling for Silvershop order */
    .silvershop-email__title {
        font-size: 1.5em;
        display: block;
        text-align: right;
        border-bottom: 1px solid #CDDDDD;
        text-transform: uppercase;
    }

    .silvershop-email__content {
        text-align: left;
        margin: auto;
    }

    .silvershop-email__sender-table {
        width: 100%;
    }

    .silvershop-email__sender-table .silvershop-email__sender,
    .silvershop-email__sender-table .silvershop-email__meta {
        width: 50%;
    }

    .silvershop-email__meta-table {
        margin-left: auto;
    }

    .silvershop-email__meta-table .silvershop-email__label {
        font-weight: bold;
    }

    .silvershop-receipt {
        border: 1px solid #CDDDDD;
        border-collapse: collapse;
        width: 100%;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        background: #fff;
        margin-top: 10px;
    }

    .silvershop-message--warning {
        margin: 4px 0 0 3px;
        padding: 5px;
        width: 92%;
        color: #DC1313;
        border: 4px solid #FF7373;
        background: #FED0D0;
    }

    .silvershop-receipt h3 {
        color: #4EA3D7;
        font-size: 15px;
        font-weight: normal;
        font-family: Tahoma, Verdana, sans-serif;
    }

    .silvershop-receipt__row--total td {
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
    }

    .silvershop-receipt__cell {
        padding: 5px;
        color: #333;
        border: 1px solid #CDDDDD;
        font-size: 12px;
    }

    .silvershop-receipt__row--summary {
        font-weight: bold;
    }

    .silvershop-receipt__cell--ordersummary {
        font-size: 1em;
        border-bottom: 1px solid #ccc;
    }

    .silvershop-receipt__cell--head {
        font-weight: bold;
        font-size: 12px;
        color: #000;
        background: #E7EFEF;
    }

    .silvershop-receipt__cell a {
        color: #4EA3D7;
        text-decoration: underline;
    }

    .silvershop-receipt__cell a:hover {
        text-decoration: none;
    }

    .silvershop-receipt__row--modifier .silvershop-receipt__cell--label {
        text-align: right;
    }

    .silvershop-receipt__cell--right {
        text-align: right;
    }

    .silvershop-receipt__cell--center {
        text-align: center;
    }

    .silvershop-receipt__cell--left,
    .silvershop-receipt__cell--head {
        text-align: left;
    }

    .silvershop-receipt--addresses td,
    .silvershop-receipt--addresses th {
        width: 50%;
    }
</style>

<html>
    <head>
        <% base_tag %>
        $MetaTags
        <% include SilverShop\Includes\OrderReceiptStyle %>
    </head>
    <body class="silvershop-printable">
        <div class="silvershop-printable__page">
            <h1 class="silvershop-printable__title">
                <%t SilverShop\Admin\OrdersAdmin.ReceiptTitle "{SiteTitle} Order {OrderNo}" SiteTitle=$SiteConfig.Title OrderNo=$Reference %>
            </h1>
            <% include SilverShop\Model\Order %>
        </div>
    </body>
</html>

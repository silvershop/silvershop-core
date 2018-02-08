<html>
    <head>
        <% base_tag %>
        $MetaTags
        <% include SilverShop\Includes\OrderReceiptStyle %>
    </head>
    <body>
        <div style="page-break-after: always;">
            <h1 class="title">
                <%t SilverShop\Admin\OrdersAdmin.ReceiptTitle "{SiteTitle} Order {OrderNo}" SiteTitle=$SiteConfig.Title OrderNo=$Reference %>
            </h1>
            <% include SilverShop\Model\Order %>
        </div>
    </body>
</html>


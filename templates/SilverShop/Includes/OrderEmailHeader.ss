<%-- Shared opening shell for every shop notification email. Opens the HTML document, pulls in the default
     flat styling, and opens the white content card. Pair with SilverShop\Includes\OrderEmailFooter; the
     email's own title cell and body rows go between the two includes. Pass an optional BEM modifier as
     $Modifier (e.g. "confirmation") to target a single email in CSS. Override this template to restyle the
     opening of every shop email at once. --%>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>$SiteConfig.Title</title>
        <% include SilverShop\Includes\OrderReceiptStyle %>
    </head>
    <body>
        <table class="silvershop-email<% if $Modifier %> silvershop-email--$Modifier<% end_if %>" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <table class="silvershop-email__content" cellspacing="0" cellpadding="0" summary="Email Information">

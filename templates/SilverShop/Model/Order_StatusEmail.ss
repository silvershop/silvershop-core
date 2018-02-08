<!-- Reference: https://github.com/mailgun/transactional-email-templates -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><%t SilverShop\ShopEmail.StatusChangeTitle "Shop Status Change" %></title>
        <style type="text/css">
            table {     /* Remove spacing around Outlook 07, 10 tables */
                border-collapse:collapse;
                mso-table-lspace:0pt;
                mso-table-rspace:0pt;
            }
            table td {border-collapse: collapse;}  /* Outlook 07 & 10 padding issue */
            tr {
                box-sizing: border-box;
                margin: 0;
            }
            td {
                box-sizing: border-box;
                margin: 0;
                vertical-align: top;
            }
            body, table#container {
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                background-color: #f6f6f6;
                font-size: 14px;
                line-height: 1.6em;
                padding: 0;
                margin: auto;
                width: 100% !important;
                height: 100%;
                box-sizing: border-box;
                -webkit-font-smoothing: antialiased;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust:100%;
            }
            table#container > tr > td {
                padding: 0 5px 0 5px;
            }
            td.max-width {
                max-width: 600px !important;
                display: block !important;
                clear: both !important;
                margin: 0 auto;
            }

            table.main {
                border: 1px solid #e9e9e9;
                border-radius: 3px;
                background-color: #fff;
                margin: 0;
            }
            td.banner {
                color: #fff;
                background-color: #659726;
                font-size: 14px;
                font-weight: 500;
                text-align: center;
                border-radius: 3px 3px 0 0;
                padding: 20px;
            }
            td#Content {
                padding: 20px;
                text-align:left;
                margin:auto;
            }
            td.content-block {
                padding: 0 0 20px;
            }
            @media only screen and (max-width: 640px) {
                body, table#container {
                    padding: 0 !important;
                    width: 100% !important;
                }
                td#Content {
                    padding: 10px !important;
                }
            }
        </style>
    </head>
    <body>
        <table id="container">
            <tr>
                <td class="max-width">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td class="banner" align="center" valign="top">
                                    <strong><%t SilverShop\ShopEmail.StatusChangeTitle 'Shop Status Change' %></strong>
                                </td>
                            </tr>
                            <tr>
                                <td id="Content" valign="top">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <% with Order %>
                                            <tr>
                                                <td class="content-block" valign="top">
                                                    <%t SilverStripe\Control\ChangePasswordEmail_ss.Hello 'Hello' %> <% if $FirstName %>$FirstName<% else %>$Member.FirstName<% end_if %>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="content-block" valign="top">
                                                    <%t SilverShop\ShopEmail.StatusChanged 'Status for order #{OrderNo} changed to "{OrderStatus}"' OrderNo=$Reference OrderStatus=$StatusI18N %>
                                                </td>
                                            </tr>
                                        <% end_with %>
                                        <tr>
                                            <td class="content-block" valign="top">
                                                $Note
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="content-block" valign="top">
                                                <%t SilverShop\ShopEmail.Regards "Kind regards" %>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="content-block" valign="top">
                                                $SiteConfig.Title<br/>
                                                $FromEmail<br/>
                                                <%t SilverShop\ShopEmail.PhoneNumber "PhoneNumber" %>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

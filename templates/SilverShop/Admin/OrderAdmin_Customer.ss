<div class="grid grid-field">
    <table class="shop-order shop-order--customer table grid-field__table">
        <thead>
            <tr class="shop-order__title">
                <th colspan="2">
                    <h2><%t SilverShop\Generic.Customer "Customer" %></h2>
                </th>
            </tr>
            <tr class="shop-order__header">
                <th><%t SilverShop\Page\AccountPage.MemberName "Name" %></th>
                <th><%t SilverShop\Page\AccountPage.MemberEmail "Email" %></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>$Name</td>
                <td>
                    <% if $LatestEmail %>
                        <a href="mailto:$LatestEmail">$LatestEmail</a>
                    <% end_if %>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="bottom-all" colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

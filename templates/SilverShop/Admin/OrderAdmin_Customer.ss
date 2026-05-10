<div class="silvershop-grid silvershop-grid-field">
    <table class="silvershop-shop-order silvershop-shop-order--customer silvershop-grid-field__table">
        <thead>
            <tr class="silvershop-shop-order__title-row">
                <th class="silvershop-shop-order__title-cell" colspan="2">
                    <h2 class="silvershop-shop-order__title"><%t SilverShop\Generic.Customer "Customer" %></h2>
                </th>
            </tr>
            <tr class="silvershop-shop-order__header">
                <th class="silvershop-shop-order__header-cell"><%t SilverShop\Page\AccountPage.MemberName "Name" %></th>
                <th class="silvershop-shop-order__header-cell"><%t SilverShop\Page\AccountPage.MemberEmail "Email" %></th>
            </tr>
        </thead>
        <tbody>
            <tr class="silvershop-shop-order__row silvershop-shop-order__row--customer">
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--name">$Name</td>
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--email">
                    <% if $LatestEmail %>
                        <a class="silvershop-shop-order__email-link" href="mailto:$LatestEmail">$LatestEmail</a>
                    <% end_if %>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="silvershop-shop-order__row silvershop-shop-order__row--bottom">
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--bottom" colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

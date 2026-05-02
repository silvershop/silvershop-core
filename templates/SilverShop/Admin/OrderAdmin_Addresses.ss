<div class="silvershop-grid silvershop-grid-field">
    <table class="silvershop-shop-order silvershop-shop-order--addresses silvershop-table silvershop-grid-field__table">
        <thead>
            <tr class="silvershop-shop-order__title">
                <th colspan="2">
                    <h2><%t SilverShop\Model\Address.SINGULARNAME "Address" %></h2>
                </th>
            </tr>
            <tr class="silvershop-shop-order__header">
                <th><%t SilverShop\Model\Order.ShipTo "Ship To" %></th>
                <th><%t SilverShop\Model\Order.BillTo "Bill To" %></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="silvershop-shop-order__address">$getShippingAddress</td>
                <td class="silvershop-shop-order__address">$getBillingAddress</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="silvershop-bottom-all" colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

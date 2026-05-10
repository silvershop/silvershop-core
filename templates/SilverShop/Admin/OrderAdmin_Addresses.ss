<div class="silvershop-grid silvershop-grid-field">
    <table class="silvershop-shop-order silvershop-shop-order--addresses silvershop-grid-field__table">
        <thead>
            <tr class="silvershop-shop-order__title-row">
                <th class="silvershop-shop-order__title-cell" colspan="2">
                    <h2 class="silvershop-shop-order__title"><%t SilverShop\Model\Address.SINGULARNAME "Address" %></h2>
                </th>
            </tr>
            <tr class="silvershop-shop-order__header">
                <th class="silvershop-shop-order__header-cell"><%t SilverShop\Model\Order.ShipTo "Ship To" %></th>
                <th class="silvershop-shop-order__header-cell"><%t SilverShop\Model\Order.BillTo "Bill To" %></th>
            </tr>
        </thead>
        <tbody>
            <tr class="silvershop-shop-order__row silvershop-shop-order__row--addresses">
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--address">$getShippingAddress</td>
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--address">$getBillingAddress</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="silvershop-shop-order__row silvershop-shop-order__row--bottom">
                <td class="silvershop-shop-order__cell silvershop-shop-order__cell--bottom" colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

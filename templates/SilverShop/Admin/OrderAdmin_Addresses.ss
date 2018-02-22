<div class="grid grid-field">
    <table class="shop-order shop-order--addresses table grid-field__table">
        <thead>
            <tr class="shop-order__title">
                <th colspan="2">
                    <h2><%t SilverShop\Model\Address.SINGULARNAME "Address" %></h2>
                </th>
            </tr>
            <tr class="shop-order__header">
                <th><%t SilverShop\Model\Order.ShipTo "Ship To" %></th>
                <th><%t SilverShop\Model\Order.BillTo "Bill To" %></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="shop-order__address">$getShippingAddress</td>
                <td class="shop-order__address">$getBillingAddress</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="bottom-all" colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

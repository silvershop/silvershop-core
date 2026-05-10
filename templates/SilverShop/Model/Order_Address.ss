<table class="silvershop-receipt silvershop-receipt--addresses">
    <thead>
        <tr class="silvershop-receipt__row silvershop-receipt__row--head">
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--address-head"><%t SilverShop\Model\Order.ShipTo "Ship To" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--address-head"><%t SilverShop\Model\Order.BillTo "Bill To" %></th>
        </tr>
    </thead>
    <tbody>
        <tr class="silvershop-receipt__row silvershop-receipt__row--addresses">
            <td class="silvershop-receipt__cell silvershop-receipt__cell--address">$getShippingAddress</td>
            <td class="silvershop-receipt__cell silvershop-receipt__cell--address">$getBillingAddress</td>
        </tr>
    </tbody>
</table>

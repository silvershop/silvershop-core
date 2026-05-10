<tfoot>
    <tr class="silvershop-receipt__row silvershop-receipt__row--summary silvershop-receipt__row--subtotal">
        <td class="silvershop-receipt__cell silvershop-receipt__cell--label" colspan="4" scope="row"><%t SilverShop\Model\Order.SubTotal "Sub-total" %></td>
        <td class="silvershop-receipt__cell silvershop-receipt__cell--right silvershop-receipt__cell--value">$SubTotal.Nice</td>
    </tr>
    <% loop $Modifiers %>
        <% if $ShowInTable %>
            <tr class="silvershop-receipt__row silvershop-receipt__row--modifier $EvenOdd $FirstLast $Classes">
                <td class="silvershop-receipt__cell silvershop-receipt__cell--label" colspan="4" scope="row">$TableTitle</td>
                <td class="silvershop-receipt__cell silvershop-receipt__cell--right silvershop-receipt__cell--value">$TableValue.Nice</td>
            </tr>
        <% end_if %>
    <% end_loop %>
    <tr class="silvershop-receipt__row silvershop-receipt__row--summary silvershop-receipt__row--total">
        <td class="silvershop-receipt__cell silvershop-receipt__cell--label" colspan="4" scope="row"><%t SilverShop\Model\Order.Total "Total" %></td>
        <td class="silvershop-receipt__cell silvershop-receipt__cell--right silvershop-receipt__cell--value">$Total.Nice $Currency</td>
    </tr>
</tfoot>

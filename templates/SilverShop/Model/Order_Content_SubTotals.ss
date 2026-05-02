<tfoot>
    <tr class="silvershop-gap silvershop-summary" id="SubTotal">
        <td colspan="4" scope="row" class="silvershop-threeColHeader silvershop-subtotal"><%t SilverShop\Model\Order.SubTotal "Sub-total" %></td>
        <td class="silvershop-right">$SubTotal.Nice</td>
    </tr>
    <% loop $Modifiers %>
        <% if $ShowInTable %>
            <tr class="silvershop-modifierRow $EvenOdd $FirstLast $Classes">
                <td colspan="4" scope="row">$TableTitle</td>
                <td class="silvershop-right">$TableValue.Nice</td>
            </tr>
        <% end_if %>
    <% end_loop %>
    <tr class="silvershop-gap silvershop-summary silvershop-total" id="Total">
        <td colspan="4" scope="row" class="silvershop-threeColHeader silvershop-total"><%t SilverShop\Model\Order.Total "Total" %></td>
        <td class="silvershop-right">$Total.Nice $Currency</td>
    </tr>
</tfoot>

<% require css("silvershop/core: client/dist/css/order.css") %>

<%-- As Order.ss is also used in emails, avoid div, paragraph and heading elements --%>
<% include SilverShop\Model\Order_Address %>
<% include SilverShop\Model\Order_Content %>
<% if $Total %>
    <% if $Payments %>
        <% include SilverShop\Model\Order_Payments %>
    <% end_if %>
    <table class="silvershop-receipt silvershop-receipt--outstanding">
        <tbody>
            <tr class="silvershop-receipt__row silvershop-receipt__row--summary silvershop-receipt__row--outstanding">
                <th class="silvershop-receipt__cell silvershop-receipt__cell--label" colspan="4" scope="row"><strong><%t SilverShop\Model\Order.TotalOutstanding "Total outstanding" %></strong></th>
                <td class="silvershop-receipt__cell silvershop-receipt__cell--value"><strong>$TotalOutstanding.Nice </strong></td>
            </tr>
        </tbody>
    </table>
<% end_if %>
<% if $Notes %>
    <table class="silvershop-receipt silvershop-receipt--notes">
        <thead>
            <tr class="silvershop-receipt__row silvershop-receipt__row--head">
                <th class="silvershop-receipt__cell silvershop-receipt__cell--head"><%t SilverShop\Model\Order.db_Notes "Notes" %></th>
            </tr>
        </thead>
        <tbody>
            <tr class="silvershop-receipt__row">
                <td class="silvershop-receipt__cell silvershop-receipt__cell--notes">$Notes</td>
            </tr>
        </tbody>
    </table>
<% end_if %>

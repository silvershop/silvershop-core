<% require css("silvershop/core: client/dist/css/order.css") %>

<%-- As Order.ss is also used in emails, avoid div, paragraph and heading elements --%>
<% include SilverShop\Model\Order_Address %>
<% include SilverShop\Model\Order_Content %>
<% if $Total %>
    <% if $Payments %>
        <% include SilverShop\Model\Order_Payments %>
    <% end_if %>
    <table id="OutstandingTable" class="silvershop-infotable">
        <tbody>
            <tr class="silvershop-gap silvershop-summary" id="Outstanding">
                <th colspan="4" scope="row" class="silvershop-threeColHeader"><strong><%t SilverShop\Model\Order.TotalOutstanding "Total outstanding" %></strong></th>
                <td class="silvershop-right"><strong>$TotalOutstanding.Nice </strong></td>
            </tr>
        </tbody>
    </table>
<% end_if %>
<% if $Notes %>
    <table id="NotesTable" class="silvershop-infotable">
        <thead>
            <tr>
                <th><%t SilverShop\Model\Order.db_Notes "Notes" %></th>
            </tr>
        </thead>
        </tbody>
            <tr>
                <td>$Notes</td>
            </tr>
        </tbody>
    </table>
<% end_if %>

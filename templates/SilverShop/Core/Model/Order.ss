<% require themedCSS("order") %>
<% require themedCSS("shop") %>
<%-- As Order.ss is also used in emails, avoid div, paragraph and heading elements --%>
<% include SilverStripe\Core\Model\Order_Address %>
<% include SilverShop\Core\Model\Order_Content %>
<% if $Total %>
    <% if $Payments %>
        <% include SilverShop\Core\Model\Order_Payments %>
    <% end_if %>
    <table id="OutstandingTable" class="infotable">
        <tbody>
            <tr class="gap summary" id="Outstanding">
                <th colspan="4" scope="row" class="threeColHeader"><strong><%t Order.TotalOutstanding "Total outstanding" %></strong></th>
                <td class="right"><strong>$TotalOutstanding.Nice </strong></td>
            </tr>
        </tbody>
    </table>
<% end_if %>
<% if $Notes %>
    <table id="NotesTable" class="infotable">
        <thead>
            <tr>
                <th><%t Order.db_Notes "Notes" %></th>
            </tr>
        </thead>
        </tbody>
            <tr>
                <td>$Notes</td>
            </tr>
        </tbody>
    </table>
<% end_if %>

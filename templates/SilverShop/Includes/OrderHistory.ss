<table class="silvershop-order-history">
    <thead class="silvershop-order-history__head">
        <tr class="silvershop-order-history__row silvershop-order-history__row--head">
            <th class="silvershop-order-history__cell silvershop-order-history__cell--reference"><%t SilverShop\Model\Order.db_Reference 'Reference' %></th>
            <th class="silvershop-order-history__cell silvershop-order-history__cell--date"><%t SilverShop\Model\Order.Date 'Date' %></th>
            <th class="silvershop-order-history__cell silvershop-order-history__cell--items"><%t SilverShop\Model\Order.has_many_Items 'Items' %></th>
            <th class="silvershop-order-history__cell silvershop-order-history__cell--total"><%t SilverShop\Model\Order.Total 'Total' %></th>
            <th class="silvershop-order-history__cell silvershop-order-history__cell--status"><%t SilverShop\Model\Order.db_Status 'Status' %></th>
            <th class="silvershop-order-history__cell silvershop-order-history__cell--actions"></th>
        </tr>
    </thead>
    <tbody class="silvershop-order-history__body">
        <% loop $PastOrders %>
        <tr class="silvershop-order-history__row silvershop-order-history__row--$Status">
            <td class="silvershop-order-history__cell silvershop-order-history__cell--reference">$Reference</td>
            <td class="silvershop-order-history__cell silvershop-order-history__cell--date">$Created.Nice</td>
            <td class="silvershop-order-history__cell silvershop-order-history__cell--items">$Items.Quantity</td>
            <td class="silvershop-order-history__cell silvershop-order-history__cell--total">$Total.Nice</td>
            <td class="silvershop-order-history__cell silvershop-order-history__cell--status">$StatusI18N</td>
            <td class="silvershop-order-history__cell silvershop-order-history__cell--actions">
                <a class="silvershop-order-history__view silvershop-button silvershop-button--small silvershop-button--primary" href="$Link">
                    <i class="silvershop-order-history__view-icon fa fa-eye"></i> <%t SilverShop\Generic.View 'view' %>
                </a>
            </td>
        </tr>
        <% end_loop %>
    </tbody>
</table>

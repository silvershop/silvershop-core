<table class="silvershop-receipt silvershop-receipt--payments">
    <thead>
        <tr class="silvershop-receipt__row silvershop-receipt__row--head silvershop-receipt__row--main-header">
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--left" colspan="4"><%t SilverShop\Payment.PaymentsHeadline "Payment(s)" %></th>
        </tr>
        <tr class="silvershop-receipt__row silvershop-receipt__row--head">
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--col-head" scope="row"><%t SilverStripe\Omnipay\Model\Payment.Date "Date" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--col-head" scope="row"><%t SilverStripe\Omnipay\Model\Payment.Amount "Amount" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--col-head" scope="row"><%t SilverStripe\Omnipay\Model\Payment.db_Status "Payment Status" %></th>
            <th class="silvershop-receipt__cell silvershop-receipt__cell--head silvershop-receipt__cell--col-head" scope="row"><%t SilverStripe\Omnipay\Model\Payment.db_Gateway "Method" %></th>
        </tr>
    </thead>
    <tbody>
        <% loop $Payments %>
            <tr class="silvershop-receipt__row silvershop-receipt__row--payment">
                <td class="silvershop-receipt__cell silvershop-receipt__cell--payment-date">$Created.Nice</td>
                <td class="silvershop-receipt__cell silvershop-receipt__cell--payment-amount">$Amount.Nice $Currency</td>
                <td class="silvershop-receipt__cell silvershop-receipt__cell--payment-status">$PaymentStatus</td>
                <td class="silvershop-receipt__cell silvershop-receipt__cell--payment-gateway">$GatewayTitle</td>
            </tr>
            <% if $ShowMessages %>
                <% loop $Messages %>
                    <tr class="silvershop-receipt__row silvershop-receipt__row--payment-message">
                        <td class="silvershop-receipt__cell silvershop-receipt__cell--payment-message" colspan="4">
                            $ClassName $Message $User.Name
                        </td>
                    </tr>
                <% end_loop %>
            <% end_if %>
        <% end_loop %>
    </tbody>
</table>

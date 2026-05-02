<table id="PaymentTable" class="silvershop-infotable">
    <thead>
        <tr class="silvershop-gap silvershop-mainHeader">
            <th colspan="4" class="silvershop-left"><%t SilverShop\Payment.PaymentsHeadline "Payment(s)" %></th>
        </tr>
        <tr>
            <th scope="row" class="silvershop-twoColHeader"><%t SilverStripe\Omnipay\Model\Payment.Date "Date" %></th>
            <th scope="row"  class="silvershop-twoColHeader"><%t SilverStripe\Omnipay\Model\Payment.Amount "Amount" %></th>
            <th scope="row"  class="silvershop-twoColHeader"><%t SilverStripe\Omnipay\Model\Payment.db_Status "Payment Status" %></th>
            <th scope="row" class="silvershop-twoColHeader"><%t SilverStripe\Omnipay\Model\Payment.db_Gateway "Method" %></th>
        </tr>
    </thead>
    <tbody>
        <% loop $Payments %>
            <tr>
                <td class="silvershop-price">$Created.Nice</td>
                <td class="silvershop-price">$Amount.Nice $Currency</td>
                <td class="silvershop-price">$PaymentStatus</td>
                <td class="silvershop-price">$GatewayTitle</td>
            </tr>
            <% if $ShowMessages %>
                <% loop $Messages %>
                    <tr>
                        <td colspan="4">
                            $ClassName $Message $User.Name
                        </td>
                    </tr>
                <% end_loop %>
            <% end_if %>
        <% end_loop %>
    </tbody>
</table>

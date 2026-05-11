<% if $SilverShopIncludeDefaultStyles %>
<% require css("silvershop/core:client/dist/css/silvershop-base.css") %>
<% require css("silvershop/core:client/dist/css/checkout.css") %>
<% end_if %>

<div class="silvershop-stepped-checkout">
    <h1 class="silvershop-stepped-checkout__title">$Title</h1>

    <% if $PaymentErrorMessage %>
        <p class="silvershop-message silvershop-message--error">
            <%t SilverShop\Page\CheckoutPage.PaymentErrorMessage 'Received error from payment gateway:' %>
            $PaymentErrorMessage
        </p>
    <% end_if %>

    <% if $Cart %>

        <div class="silvershop-stepped-checkout__layout">
            <div class="silvershop-stepped-checkout__main">

                <div class="silvershop-stepped-checkout__steps">

                    <div class="silvershop-stepped-checkout__step silvershop-stepped-checkout__step--cart">
                        <div class="silvershop-stepped-checkout__step-heading">
                            <h3 class="silvershop-stepped-checkout__step-toggle">
                                <a class="silvershop-stepped-checkout__step-link" href="cart" title="edit cart contents">Cart</a>
                            </h3>
                        </div>
                        <div class="silvershop-stepped-checkout__step-body">
                            <div class="silvershop-stepped-checkout__step-inner">
                                <% with $Cart %>
                                    <% include SilverShop\Cart\Cart %>
                                <% end_with %>
                            </div>
                        </div>
                    </div>

                    <div class="silvershop-stepped-checkout__step silvershop-stepped-checkout__step--contact<% if $IsPastStep('contactdetails') %> silvershop-stepped-checkout__step--past<% end_if %><% if $IsCurrentStep('contactdetails') %> silvershop-stepped-checkout__step--current<% end_if %><% if $IsFutureStep('contactdetails') %> silvershop-stepped-checkout__step--future<% end_if %>">
                        <div class="silvershop-stepped-checkout__step-heading">
                            <% if $IsPastStep('contactdetails') %>
                                <h3 class="silvershop-stepped-checkout__step-toggle">
                                    <a class="silvershop-stepped-checkout__step-link" href="$Link('contactdetails')" title="edit contact details">Contact</a>
                                </h3>
                            <% else %>
                                <h3 class="silvershop-stepped-checkout__step-toggle">Contact</h3>
                            <% end_if %>
                        </div>
                        <% if $IsFutureStep('contactdetails') %>

                        <% else %>
                            <div class="silvershop-stepped-checkout__step-body">
                                <div class="silvershop-stepped-checkout__step-inner">
                                    <% if $IsCurrentStep('contactdetails') %>
                                        <p class="silvershop-stepped-checkout__step-prompt"><%t SilverShop\Checkout\Step\Address.SupplyContactInformation "Supply your contact information" %></p>
                                        $OrderForm
                                    <% end_if %>
                                    <% if $IsPastStep('contactdetails') %>
                                        <% with $Cart %>
                                            $Name ($Email)
                                        <% end_with %>
                                    <% end_if %>
                                </div>
                            </div>
                        <% end_if %>
                    </div>

                    <div class="silvershop-stepped-checkout__step silvershop-stepped-checkout__step--address<% if $IsPastStep('shippingaddress') %> silvershop-stepped-checkout__step--past<% end_if %><% if $IsCurrentStep('shippingaddress') %> silvershop-stepped-checkout__step--current<% end_if %><% if $IsFutureStep('shippingaddress') %> silvershop-stepped-checkout__step--future<% end_if %>">
                        <div class="silvershop-stepped-checkout__step-heading">
                            <% if $IsPastStep('shippingaddress') %>
                                <h3 class="silvershop-stepped-checkout__step-toggle">
                                    <a class="silvershop-stepped-checkout__step-link" title="edit address(es)" href="$Link('shippingaddress')">
                                        <%t SilverShop\Model\Address.SINGULARNAME "Address" %>
                                    </a>
                                </h3>
                            <% else %>
                                <h3 class="silvershop-stepped-checkout__step-toggle"><%t SilverShop\Model\Address.SINGULARNAME "Address" %></h3>
                            <% end_if %>
                        </div>
                        <% if $IsFutureStep('shippingaddress') %>

                        <% else %>
                            <div class="silvershop-stepped-checkout__step-body">
                                <div class="silvershop-stepped-checkout__step-inner">
                                    <% if $IsCurrentStep('shippingaddress') %>
                                        <p class="silvershop-stepped-checkout__step-prompt"><%t SilverShop\Checkout\Step\Address.EnterShippingAddress "Please enter your shipping address details." %></p>
                                        $OrderForm
                                    <% end_if %>
                                    <% if $IsPastStep('shippingaddress') %>
                                        <div class="silvershop-stepped-checkout__addresses">
                                            <div class="silvershop-stepped-checkout__address silvershop-stepped-checkout__address--shipping">
                                                <% with $Cart %>
                                                    <h4 class="silvershop-stepped-checkout__address-title"><%t SilverShop\Checkout\Step\Address.ShipTo "Ship To:" %></h4>
                                                    $ShippingAddress
                                                <% end_with %>
                                            </div>
                                            <div class="silvershop-stepped-checkout__address silvershop-stepped-checkout__address--billing">
                                                <h4 class="silvershop-stepped-checkout__address-title"><%t SilverShop\Checkout\Step\Address.BillTo "Bill To:" %></h4>
                                                <% if $IsCurrentStep('billingaddress') %>
                                                    $OrderForm
                                                <% else %>
                                                    <% with $Cart %>
                                                        $BillingAddress
                                                    <% end_with %>
                                                <% end_if %>
                                            </div>
                                        </div>
                                    <% end_if %>
                                </div>
                            </div>
                        <% end_if %>
                    </div>

                    <div class="silvershop-stepped-checkout__step silvershop-stepped-checkout__step--shipping<% if $IsPastStep('shippingmethod') %> silvershop-stepped-checkout__step--past<% end_if %><% if $IsCurrentStep('shippingmethod') %> silvershop-stepped-checkout__step--current<% end_if %><% if $IsFutureStep('shippingmethod') %> silvershop-stepped-checkout__step--future<% end_if %>">
                        <div class="silvershop-stepped-checkout__step-heading">
                            <% if $IsPastStep('shippingmethod') %>
                                <h3 class="silvershop-stepped-checkout__step-toggle">
                                    <a class="silvershop-stepped-checkout__step-link" title="choose shipping method" href="$Link('shippingmethod')">
                                        <%t SilverShop\Checkout\Step\CheckoutStep.Shipping "Shipping" %>
                                    </a>
                                </h3>
                            <% else %>
                                <h3 class="silvershop-stepped-checkout__step-toggle"><%t SilverShop\Checkout\Step\CheckoutStep.Shipping "Shipping" %></h3>
                            <% end_if %>
                        </div>
                        <% if $IsFutureStep('shippingmethod') %>

                        <% else %>
                            <div class="silvershop-stepped-checkout__step-body">
                                <div class="silvershop-stepped-checkout__step-inner">
                                    <% if $IsCurrentStep('shippingmethod') %>
                                        $OrderForm
                                    <% end_if %>
                                    <% if $IsPastStep('shippingmethod') %>
                                        <% with $Cart %>
                                            <p class="silvershop-stepped-checkout__shipping-method">$ShippingMethod.Title</p>
                                        <% end_with %>
                                    <% end_if %>
                                </div>
                            </div>
                        <% end_if %>
                    </div>

                    <div class="silvershop-stepped-checkout__step silvershop-stepped-checkout__step--payment<% if $IsPastStep('paymentmethod') %> silvershop-stepped-checkout__step--past<% end_if %><% if $IsCurrentStep('paymentmethod') %> silvershop-stepped-checkout__step--current<% end_if %><% if $IsFutureStep('paymentmethod') %> silvershop-stepped-checkout__step--future<% end_if %>">
                        <div class="silvershop-stepped-checkout__step-heading">
                            <% if $IsPastStep('paymentmethod') %>
                                <h3 class="silvershop-stepped-checkout__step-toggle">
                                    <a class="silvershop-stepped-checkout__step-link" title="choose payment method" href="$Link('paymentmethod')">
                                        <%t SilverShop\Forms\OrderActionsForm.PaymentMethod "Payment Method" %>
                                    </a>
                                </h3>
                            <% else %>
                                <h3 class="silvershop-stepped-checkout__step-toggle"><%t SilverShop\Forms\OrderActionsForm.PaymentMethod "Payment Method" %></h3>
                            <% end_if %>
                        </div>
                        <% if $IsFutureStep('paymentmethod') %>

                        <% else %>
                            <div class="silvershop-stepped-checkout__step-body">
                                <div class="silvershop-stepped-checkout__step-inner">
                                    <% if $IsCurrentStep('paymentmethod') %>
                                        $OrderForm
                                    <% end_if %>
                                    <% if $IsPastStep('paymentmethod') %>
                                        $SelectedPaymentMethod
                                    <% end_if %>
                                </div>
                            </div>
                        <% end_if %>
                    </div>

                    <div class="silvershop-stepped-checkout__step silvershop-stepped-checkout__step--summary<% if $IsCurrentStep('summary') %> silvershop-stepped-checkout__step--current<% end_if %><% if $IsFutureStep('summary') %> silvershop-stepped-checkout__step--future<% end_if %>">
                        <div class="silvershop-stepped-checkout__step-heading">
                            <h3 class="silvershop-stepped-checkout__step-toggle"><%t SilverShop\Checkout\Step\CheckoutStep.Summary "Summary" %></h3>
                        </div>
                        <% if $IsFutureStep('summary') %>

                        <% else %>
                            <div class="silvershop-stepped-checkout__step-body">
                                <div class="silvershop-stepped-checkout__step-inner">
                                    <% if $IsCurrentStep('summary') %>
                                        <% with $Cart %>
                                            <table class="silvershop-stepped-checkout__summary">
                                                <tfoot>
                                                    <% loop $Modifiers %>
                                                        <% if $ShowInTable %>
                                                            <tr class="silvershop-stepped-checkout__summary-row silvershop-stepped-checkout__summary-row--modifier $EvenOdd $FirstLast $ClassName">
                                                                <td class="silvershop-stepped-checkout__summary-label" colspan="3">$TableTitle</td>
                                                                <td class="silvershop-stepped-checkout__summary-value">$TableValue.Nice</td>
                                                            </tr>
                                                        <% end_if %>
                                                    <% end_loop %>
                                                    <tr class="silvershop-stepped-checkout__summary-row silvershop-stepped-checkout__summary-row--total">
                                                        <th class="silvershop-stepped-checkout__summary-label" colspan="3"><%t SilverShop\Model\Order.GrandTotal "Grand Total" %></th>
                                                        <td class="silvershop-stepped-checkout__summary-value">$Total.Nice $Currency</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        <% end_with %>
                                        $OrderForm
                                    <% end_if %>
                                </div>
                            </div>
                        <% end_if %>
                    </div>

                </div>

            </div>
        </div>

    <% else %>

        <div class="silvershop-message silvershop-message--warning silvershop-message--block">
            <h4 class="silvershop-message__heading"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></h4>
        </div>

        <% if $ContinueLink %>
            <a class="silvershop-stepped-checkout__continue silvershop-button silvershop-button--primary" href="$ContinueLink">
                <%t SilverShop\Cart\ShoppingCart.ContinueShopping 'Continue Shopping' %>
            </a>
        <% end_if %>

    <% end_if %>
</div>

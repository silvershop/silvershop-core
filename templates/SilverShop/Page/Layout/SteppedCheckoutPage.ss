<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1>$Title</h1>

<% if $PaymentErrorMessage %>
    <p class="silvershop-message silvershop-error">
        <%t SilverShop\Page\CheckoutPage.PaymentErrorMessage 'Received error from payment gateway:' %>
        $PaymentErrorMessage
    </p>
<% end_if %>

<% if $Cart %>

    <div class="silvershop-row">
        <div class="silvershop-span10">

            <div id="Checkout" class="silvershop-accordion">

                <div class="silvershop-accordion-group">
                    <div class="silvershop-accordion-heading">
                        <h3 class="silvershop-accordion-toggle" id="cont">
                            <a href="cart" title="edit cart contents">Cart</a></h3>
                    </div>
                    <div class="silvershop-accordion-body">
                        <div class="silvershop-accordion-inner">
                            <% with $Cart %>
                                <% include SilverShop\Cart\Cart %>
                            <% end_with %>
                        </div>
                    </div>
                </div>

                <div class="silvershop-accordion-group">
                    <div class="silvershop-accordion-heading">
                        <% if $IsPastStep('contactdetails') %>
                            <h3><a href="$Link('contactdetails')" class="silvershop-accordion-toggle" title="edit contact details">Contact</a></h3>
                        <% else %>
                            <h3 class="silvershop-accordion-toggle">Contact</h3>
                        <% end_if %>
                    </div>
                    <% if $IsFutureStep('contactdetails') %>

                    <% else %>
                        <div class="silvershop-accordion-body">
                            <div class="silvershop-accordion-inner">
                                <% if $IsCurrentStep('contactdetails') %>
                                    <p><%t SilverShop\Checkout\Step\Address.SupplyContactInformation "Supply your contact information" %></p>
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

                <div class="silvershop-accordion-group">
                    <div class="silvershop-accordion-heading">
                        <% if $IsPastStep('shippingaddress') %>
                            <h3><a class="silvershop-accordion-toggle" title="edit address(es)" href="$Link('shippingaddress')">
                                <%t SilverShop\Model\Address.SINGULARNAME "Address" %>
                            </a></h3>
                        <% else %>
                            <h3 class="silvershop-accordion-toggle"><%t SilverShop\Model\Address.SINGULARNAME "Address" %></h3>
                        <% end_if %>
                    </div>
                    <% if $IsFutureStep('shippingaddress') %>

                    <% else %>
                        <div class="silvershop-accordion-body">
                            <div class="silvershop-accordion-inner">
                                <% if $IsCurrentStep('shippingaddress') %>
                                    <p><%t SilverShop\Checkout\Step\Address.EnterShippingAddress "Please enter your shipping address details." %></p>
                                    $OrderForm
                                <% end_if %>
                                <% if $IsPastStep('shippingaddress') %>
                                    <div class="silvershop-row">
                                        <div class="silvershop-span4">
                                            <% with $Cart %>
                                                <h4><%t SilverShop\Checkout\Step\Address.ShipTo "Ship To:" %></h4>
                                                $ShippingAddress
                                            <% end_with %>
                                        </div>
                                        <div class="silvershop-span4">
                                        <h4><%t SilverShop\Checkout\Step\Address.BillTo "Bill To:" %></h4>
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

                <div class="silvershop-accordion-group">
                    <div class="silvershop-accordion-heading">
                        <% if $IsPastStep('shippingmethod') %>
                            <h3><a class="silvershop-accordion-toggle" title="choose shipping method" href="$Link('shippingmethod')">
                                <%t SilverShop\Checkout\Step\CheckoutStep.Shipping "Shipping" %>
                            </a></h3>
                        <% else %>
                            <h3 class="silvershop-accordion-toggle"><%t SilverShop\Checkout\Step\CheckoutStep.Shipping "Shipping" %></h3>
                        <% end_if %>
                    </div>
                    <% if $IsFutureStep('shippingmethod') %>

                    <% else %>
                        <div class="silvershop-accordion-body">
                            <div class="silvershop-accordion-inner">
                                <% if $IsCurrentStep('shippingmethod') %>
                                    $OrderForm
                                <% end_if %>
                                <% if $IsPastStep('shippingmethod') %>
                                    <% with $Cart %>
                                        <p>$ShippingMethod.Title</p>
                                    <% end_with %>
                                <% end_if %>
                            </div>
                        </div>
                    <% end_if %>
                </div>

                <div class="silvershop-accordion-group">
                    <div class="silvershop-accordion-heading">
                        <% if $IsPastStep('paymentmethod') %>
                            <h3><a class="silvershop-accordion-toggle" title="choose payment method" href="$Link('paymentmethod')">
                                <%t SilverShop\Forms\OrderActionsForm.PaymentMethod "Payment Method" %>
                            </a></h3>
                        <% else %>
                            <h3 class="silvershop-accordion-toggle"><%t SilverShop\Forms\OrderActionsForm.PaymentMethod "Payment Method" %></h3>
                        <% end_if %>
                    </div>
                    <% if $IsFutureStep('paymentmethod') %>

                    <% else %>
                        <div class="silvershop-accordion-body">
                            <div class="silvershop-accordion-inner">
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

                <div class="silvershop-accordion-group">
                    <div class="silvershop-accordion-heading">
                        <h3 class="silvershop-accordion-toggle"><%t SilverShop\Checkout\Step\CheckoutStep.Summary "Summary" %></h3>
                    </div>
                    <% if $IsFutureStep('summary') %>

                    <% else %>
                        <div class="silvershop-accordion-body">
                            <div class="silvershop-accordion-inner">
                                <% if $IsCurrentStep('summary') %>
                                    <% with $Cart %>
                                        <table class="silvershop-table">
                                            <tfoot>
                                                <% loop $Modifiers %>
                                                    <% if $ShowInTable %>
                                                <tr class="silvershop-modifierRow $EvenOdd $FirstLast $ClassName">
                                                    <td colspan="3">$TableTitle</td>
                                                    <td>$TableValue.Nice</td>
                                                </tr>
                                                    <% end_if %>
                                                <% end_loop %>
                                                <tr>
                                                    <th colspan="3"><%t SilverShop\Model\Order.GrandTotal "Grand Total" %></th>
                                                    <td>$Total.Nice $Currency</td>
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

    <div class="silvershop-message silvershop-warning silvershop-alert silvershop-alert-block silvershop-alert-info">
        <h4 class="silvershop-alert-heading"><%t SilverShop\Cart\ShoppingCart.NoItems "There are no items in your cart." %></h4>
    </div>

    <% if $ContinueLink %>
    <a class="silvershop-continuelink silvershop-btn silvershop-btn-primary" href="$ContinueLink">
        <i class="silvershop-icon-arrow-left silvershop-icon-white"></i>
        <%t SilverShop\Cart\ShoppingCart.ContinueShopping 'Continue Shopping' %>
    </a>
    <% end_if %>

<% end_if %>

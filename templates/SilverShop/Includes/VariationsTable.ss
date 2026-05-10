<form method="post" action="$VariationsBulkAddLink" class="silvershop-variations-table-form">
    <input type="hidden" name="ProductID" value="$ID" />
    <% if $VariationsBulkSecurityTokenRequired %>
        <input type="hidden" name="$VariationsBulkSecurityTokenName.XML" value="$VariationsBulkSecurityTokenValue.ATT" />
    <% end_if %>
    <table class="silvershop-variations-table">
        <thead class="silvershop-variations-table__head">
            <tr class="silvershop-variations-table__row silvershop-variations-table__row--head">
                <th class="silvershop-variations-table__cell silvershop-variations-table__cell--variation"><%t SilverShop\Model\ProductVariation.SINGULARNAME "Variation" %></th>
                <th class="silvershop-variations-table__cell silvershop-variations-table__cell--price"><%t SilverShop\Page\Product.Price "Price" %></th>
                <% if $canPurchase %>
                    <th class="silvershop-variations-table__cell silvershop-variations-table__cell--quantity"><%t SilverShop\Model\Order.Quantity "Quantity" %></th>
                <% end_if %>
            </tr>
        </thead>
        <tbody class="silvershop-variations-table__body">
            <% loop $Variations %>
                <tr class="silvershop-variations-table__row">
                    <td class="silvershop-variations-table__cell silvershop-variations-table__cell--variation">$Title.XML</td>
                    <td class="silvershop-variations-table__cell silvershop-variations-table__cell--price">$Price.Nice $Currency</td>
                    <td class="silvershop-variations-table__cell silvershop-variations-table__cell--quantity">
                    <% if $canPurchase %>
                        <% if $IsInCart %>
                            <% with $Item %>
                                $QuantityField
                            <% end_with %>
                        <% else %>
                            <div class="silvershop-variations-table__add-row">
                                <label class="silvershop-variations-table__qty-label">
                                    <span class="silvershop-variations-table__qty-label-text"><%t SilverShop\Model\Order.Quantity "Quantity" %></span>
                                    <input class="silvershop-variations-table__qty-input" type="number" name="VariantQuantity[$ID]" min="0" step="1" value="0" inputmode="numeric" autocomplete="off" aria-label="<%t SilverShop\Model\Order.Quantity "Quantity" %>" />
                                </label>
                            </div>
                        <% end_if %>

                    <% end_if %>
                    </td>
                </tr>
            <% end_loop %>
        </tbody>
        <% if $ShowVariationsBulkAddButton %>
            <tfoot class="silvershop-variations-table__foot">
                <tr class="silvershop-variations-table__row silvershop-variations-table__row--foot">
                    <td class="silvershop-variations-table__cell silvershop-variations-table__cell--bulk-add" <% if $canPurchase %>colspan="3"<% else %>colspan="2"<% end_if %>>
                        <button type="submit" class="silvershop-variations-table__bulk-add-btn silvershop-variations-table__add-btn">
                            <%t SilverShop\Page\Product.AddVariationsToCart "Add to cart" %>
                        </button>
                    </td>
                </tr>
            </tfoot>
        <% end_if %>
    </table>
</form>

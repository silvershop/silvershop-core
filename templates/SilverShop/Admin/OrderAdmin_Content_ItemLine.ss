<tr class="silvershop-shop-order__row silvershop-shop-order__row--item $EvenOdd $FirstLast">
    <td class="silvershop-shop-order__cell silvershop-shop-order__cell--image">
        <% if $Buyable && $Buyable.Image %>
            <div class="silvershop-shop-order__image">
                <a class="silvershop-shop-order__image-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Buyable.Title %>">
                    <img class="silvershop-shop-order__image-img" src="$Buyable.Image.ScaleWidth(45).AbsoluteURL" alt="$Buyable.Title"/>
                </a>
            </div>
        <% end_if %>
    </td>
    <td class="silvershop-shop-order__cell silvershop-shop-order__cell--product">
        <strong class="silvershop-shop-order__product-title">
        <% if $Link %>
            <a class="silvershop-shop-order__product-link" href="$Link" target="new">$TableTitle</a>
        <% else %>
            $TableTitle
        <% end_if %>
        </strong>
        <% if $SubTitle %><div class="silvershop-shop-order__subtitle">$SubTitle</div><% end_if %>
        <% if $Buyable.InternalItemID %><div class="silvershop-shop-order__sku"><%t SilverShop\Page\Product.ProductCodeShort "SKU" %>: $Buyable.InternalItemID</div><% end_if %>
    </td>
    <td class="silvershop-shop-order__cell silvershop-shop-order__cell--unit-price">$UnitPrice.Nice</td>
    <td class="silvershop-shop-order__cell silvershop-shop-order__cell--quantity silvershop-shop-order__cell--count-$Quantity">$Quantity</td>
    <td class="silvershop-shop-order__cell silvershop-shop-order__cell--item-total">$Total.Nice</td>
</tr>

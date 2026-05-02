<tr class="silvershop-shop-order__itemline $EvenOdd $FirstLast">
    <td>
        <% if $Buyable && $Buyable.Image %>
            <div class="silvershop-shop-order__image">
                <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$Buyable.Title %>">
                    <img src="$Buyable.Image.ScaleWidth(45).AbsoluteURL" alt="$Buyable.Title"/>
                </a>
            </div>
        <% end_if %>
    </td>
    <td class="silvershop-shop-order__product silvershop-shop-order__title">
        <strong>
        <% if $Link %>
            <a href="$Link" target="new">$TableTitle</a>
        <% else %>
            $TableTitle
        <% end_if %>
        </strong>
        <% if $SubTitle %><div class="silvershop-shop-order__subtitle">$SubTitle</div><% end_if %>
        <% if $Buyable.InternalItemID %><div class="silvershop-shop-order__sku"><%t SilverShop\Page\Product.ProductCodeShort "SKU" %>: $Buyable.InternalItemID</div><% end_if %>
    </td>
    <td class="silvershop-shop-order__unitprice">$UnitPrice.Nice</td>
    <td class="silvershop-shop-order__quantity silvershop-count-$Quantity">$Quantity</td>
    <td class="silvershop-shop-order__item-total">$Total.Nice</td>
</tr>

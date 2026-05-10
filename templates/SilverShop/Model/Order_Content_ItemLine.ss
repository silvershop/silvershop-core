<tr class="silvershop-receipt__row silvershop-receipt__row--item $EvenOdd $FirstLast">
    <td class="silvershop-receipt__cell silvershop-receipt__cell--image">
        <% if $Image %>
            <a class="silvershop-receipt__image-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">
                <img class="silvershop-receipt__image" src="<% with $Image.ScaleWidth(45) %>$Me.AbsoluteURL<% end_with %>" alt="$Buyable.Title"/>
            </a>
        <% end_if %>
    </td>
    <td class="silvershop-receipt__cell silvershop-receipt__cell--product" scope="row">
        <% if $Link %>
            <a class="silvershop-receipt__product-link" href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
        <% else %>
            $TableTitle
        <% end_if %>
        <% if $SubTitle %>
            <span class="silvershop-receipt__subtitle">$SubTitle</span>
        <% end_if %>
    </td>
    <td class="silvershop-receipt__cell silvershop-receipt__cell--center silvershop-receipt__cell--unit-price">$UnitPrice.Nice</td>
    <td class="silvershop-receipt__cell silvershop-receipt__cell--center silvershop-receipt__cell--quantity">$Quantity</td>
    <td class="silvershop-receipt__cell silvershop-receipt__cell--right silvershop-receipt__cell--total">$Total.Nice</td>
</tr>

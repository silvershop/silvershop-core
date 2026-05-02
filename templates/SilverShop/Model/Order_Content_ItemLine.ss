<tr class="silvershop-itemRow $EvenOdd $FirstLast">
    <td class="silvershop-image">
        <% if $Image %>
            <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">
                <img src="<% with $Image.ScaleWidth(45) %>$Me.AbsoluteURL<% end_with %>" alt="$Buyable.Title"/>
            </a>
        <% end_if %>
    </td>
    <td class="silvershop-product silvershop-title" scope="row">
        <% if $Link %>
            <a href="$Link" title="<%t SilverShop\Generic.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
        <% else %>
            $TableTitle
        <% end_if %>
        <% if $SubTitle %>
            <span class="silvershop-subtitle">$SubTitle</span>
        <% end_if %>
    </td>
    <td class="silvershop-center silvershop-unitprice">$UnitPrice.Nice</td>
    <td class="silvershop-center silvershop-quantity">$Quantity</td>
    <td class="silvershop-right silvershop-total">$Total.Nice</td>
</tr>

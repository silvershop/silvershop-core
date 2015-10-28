<tr class="order-content-itemline ss-gridfield-item ss-gridfield-{$EvenOdd} $FirstLast">
	<td>
		<% if $Image %>
			<div class="image">
				<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
					<img src="<% with $Image.setWidth(45) %>$Me.AbsoluteURL<% end_with %>" alt="$Buyable.Title"/>
				</a>
			</div>
		<% end_if %>
	</td>
	<td class="product title">
		<strong>
		<% if $Link %>
			<a href="$Link" target="new">$TableTitle</a>
		<% else %>
			$TableTitle
		<% end_if %>
		</strong>
		<% if $SubTitle %><div class="subtitle">$SubTitle</div><% end_if %>
		<% if $Buyable.InternalItemID %><div class="sku"><%t Product.CODE_SHORT "SKU" %>: $Buyable.InternalItemID</div><% end_if %>
	</td>
	<td class="unitprice">$UnitPrice.Nice</td>
	<td class="quantity count-$Quantity">$Quantity</td>
	<td class="total">$Total.Nice</td>
</tr>

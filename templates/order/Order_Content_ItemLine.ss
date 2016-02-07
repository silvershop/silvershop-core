<tr class="itemRow $EvenOdd $FirstLast">
	<td class="image">
		<% if $Image %>
			<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
				<img src="<% with $Image.setWidth(45) %>$Me.AbsoluteURL<% end_with %>" alt="$Buyable.Title"/>
			</a>
		<% end_if %>
	</td>
	<td class="product title" scope="row">
		<% if $Link %>
			<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">$TableTitle</a>
		<% else %>
			$TableTitle
		<% end_if %>
		<% if $SubTitle %>
			<span class="subtitle">$SubTitle</span>
		<% end_if %>
	</td>
	<td class="center unitprice">$UnitPrice.Nice</td>
	<td class="center quantity">$Quantity</td>
	<td class="right total">$Total.Nice</td>
</tr>

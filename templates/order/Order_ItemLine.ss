<tr  class="itemRow $EvenOdd $FirstLast">
	<td>
		<% if $Product.Image %>
			<div class="image">
				<a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">
					<% with $Product %>
						<img src="<% with $Image.setWidth(45) %>$Me.AbsoluteURL<% end_with %>" alt="$Title"/>
					<% end_with %>
				</a>
			</div>
		<% end_if %>
	</td>
	<td class="product title" scope="row">
		<h5>
		<% if $Link %>
			<a href="$Link" title="<%t Shop.ReadMoreTitle "Click here to read more on &quot;{Title}&quot;" Title=$TableTitle %>">$TableTitle</a>
		<% else %>
			$TableTitle
		<% end_if %>
		</h5>
		<% if $SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
	</td>
	<td class="center unitprice">$UnitPrice.Nice</td>
	<td class="center quantity">$Quantity</td>
	<td class="right total">$Total.Nice</td>
</tr>

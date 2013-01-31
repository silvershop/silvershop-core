<tr  class="itemRow $EvenOdd $FirstLast">
	<td>
		<% if Product.Image %>
			<div class="image">
				<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
					<% control Product %>
						<img src="<% control Image.setWidth(45) %>$Me.AbsoluteURL<% end_control %>" alt="$Title"/>
					<% end_control %>
				</a>
			</div>
		<% end_if %>
	</td>
	<td class="product title" scope="row">
		<h5>
		<% if Link %>
			<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">$TableTitle</a>
		<% else %>
			$TableTitle
		<% end_if %>
		</h5>
		<% if SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
	</td>
	<td class="center unitprice">$UnitPrice.Nice</td>
	<td class="center quantity">$Quantity</td>
	<td class="right total">$Total.Nice</td>
</tr>
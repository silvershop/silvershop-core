<table id="InformationTable" class="ordercontent">
	<colgroup class="image"/>
	<colgroup class="product title"/>
	<colgroup class="unitprice" />
	<colgroup class="quantity" />
	<colgroup class="total"/>
	<colgroup class="remove"/>
	<thead>
		<tr>
			<th scope="col"></th>
			<th scope="col"><% _t("PRODUCT","Product") %></th>
			<th scope="col"><% _t("UNITPRICE","Unit Price") %></th>
			<th scope="col"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<% control Items %>
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
				<h3>
				<% if Link %>
					<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">$TableTitle</a>
				<% else %>
					$TableTitle
				<% end_if %>
				</h3>
				<% if SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
			</td>
			<td class="right unitprice">$UnitPrice.Nice</td>
			<td class="center quantity">$Quantity</td>
			<td class="right total">$Total.Nice</td>
		</tr>
		<% end_control %>
		<tr class="gap summary" id="SubTotal">
			<td colspan="3" scope="row" class="threeColHeader subtotal"><% _t("SUBTOTAL","Sub-total") %></td>
			<td class="right">$SubTotal.Nice</td>
		</tr>
		<% control Modifiers %>
			<% if ShowInTable %>
		<tr class="modifierRow $EvenOdd $FirstLast $Classes">
			<td colspan="3" scope="row">$TableTitle</td>
			<td class="right">$TableValue.Nice</td>
		</tr>
			<% end_if %>
		<% end_control %>
		<tr class="gap summary total" id="Total">
			<td colspan="3" scope="row" class="threeColHeader total"><% _t("TOTAL","Total") %></td>
			<td class="right">$Total.Nice $Currency</td>
		</tr>
	</tbody>
</table>
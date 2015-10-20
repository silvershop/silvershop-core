<% require themedCSS(cart,shop) %>
<% if $Items %>
	<table class="cart" summary="<% _t("TABLESUMMARY","Current contents of your cart.") %>">
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
				<% if $Editable %>
					<th scope="col"><% _t("REMOVE","Remove") %></th>
				<% end_if %>
			</tr>
		</thead>
		<tbody>
			<% loop $Items %><% if $ShowInTable %>
				<tr id="$TableID" class="$Classes $EvenOdd $FirstLast">
					<td>
						<% if $Image %>
							<div class="image">
								<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Buyable.Title) %>">
									$Image.setWidth(45)
								</a>
							</div>
						<% end_if %>
					</td>
					<td id="$TableTitleID">
						<h3>
						<% if $Link %>
							<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">$TableTitle</a>
						<% else %>
							$TableTitle
						<% end_if %>
						</h3>
						<% if $SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
						<% if $Product.Variations && $Editable %>
							Change: $VariationField
						<% end_if %>
					</td>
					<td>$UnitPrice.Nice</td>
					<td><% if $Editable %>$QuantityField<% else %>$Quantity<% end_if %></td>
					<td id="$TableTotalID">$Total.Nice</td>
					<% if $Editable %>
						<td>
							<% if $RemoveField %>
								$RemoveField
							<% else %>
								<a href="$removeallLink" title="<% sprintf(_t("REMOVEALL","Remove all of &quot;%s&quot; from your cart"),$TableTitle) %>">
									<img src="shop/images/remove.gif" alt="x"/>
								</a>
							<% end_if %>

						</td>
					<% end_if %>
				</tr>
			<% end_if %><% end_loop %>
		</tbody>
		<tfoot>
			<tr class="subtotal">
				<th colspan="4" scope="row"><% _t("SUBTOTAL","Sub-total") %></th>
				<td id="$TableSubTotalID">$SubTotal.Nice</td>
				<% if $Editable %><td>&nbsp;</td><% end_if %>
			</tr>
			<% if $ShowSubtotals %>
				<% if $Modifiers %>
					<% loop $Modifiers %>
						<% if $ShowInTable %>
							<tr id="$TableID" class="$Classes">
								<th id="$TableTitleID" colspan="4" scope="row">
									<% if $Link %>
										<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
									<% else %>
										$TableTitle
									<% end_if %>
								</th>
								<td id="$TableTotalID">$TableValue.Nice</td>
								<% if $Editable %>
									<td>
										<% if $CanRemove %>
											<strong>
												<a class="ajaxQuantityLink" href="$removeLink" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your order"),$TableTitle) %>">
													<img src="shop/images/remove.gif" alt="x"/>
												</a>
											</strong>
										<% end_if %>
									</td>
								<% end_if %>
							</tr>
							<% if $Form %>
								<tr>
									<td colspan="5">$Form</td><td colspan="10"></td>
								</tr>
							<% end_if %>
						<% end_if %>
					<% end_loop %>
				<% end_if %>
				<tr class="gap Total">
					<th colspan="4" scope="row"><% _t("TOTAL","Total") %></th>
					<td id="$TableTotalID"><span class="value">$Total.Nice</span> <span class="currency">$Currency</span></td>
					<% if $Editable %><td>&nbsp;</td><% end_if %>
				</tr>
			<% end_if %>
		</tfoot>
	</table>
<% else %>
	<p class="message warning">
		<% _t("NOITEMS","There are no items in your cart.") %>
	</p>
<% end_if %>

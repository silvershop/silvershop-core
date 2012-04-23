<% if Items %>
<h3 class="orderInfo"><% _t("ORDERINFORMATION","Order Information") %></h3>

<table id="InformationTable" class="orderconent editable" summary="<% _t("TABLESUMMARY","The contents of your cart are displayed in this form and summary of all fees associated with an order and a rundown of payments options.") %>">
	<colgroup>
		<col class="product title left"/>
		<col class="quantity center" />
		<col class="unitprice center" />
		<col class="total right"/>
		<col class="remove center"/>
	</colgroup>	
	<thead>
		<tr>
			<th scope="col"><% _t("PRODUCT","Product") %></th>
			<th scope="col"><% _t("QUANTITY", "Quantity") %></th>
			<th scope="col"><% _t("PRICE","Price") %> ($Currency)</th>
			<th scope="col"><% _t("TOTALPRICE","Total Price") %> ($Currency)</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<% control Items %><% if ShowInTable %>
			<tr id="$TableID" class="$Classes">
				<td id="$TableTitleID">
					<% if Product.Image %>
						<div class="image">
							<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
								<% control Product %>$Image.setWidth(45)<% end_control %>
							</a>
						</div>
					<% end_if %>
					<h3>
					<% if Link %>
						<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">$TableTitle</a>
					<% else %>
						$TableTitle
					<% end_if %>
					</h3>
					<% if SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
				</td>
				<td>$QuantityField</td>
				<td>$UnitPrice.Nice</td>
				<td id="$TableTotalID">$Total.Nice</td>
				<td>
					<a href="$removeallLink" title="<% sprintf(_t("REMOVEALL","Remove all of &quot;%s&quot; from your cart"),$TableTitle) %>">
						<img src="shop/images/remove.gif" alt="x"/>
					</a>
				</td>
			</tr>
		<% end_if %><% end_control %>
	</tbody>
	<tfoot>
		<tr class="subtotal">
			<th colspan="3" scope="row"><% _t("SUBTOTAL","Sub-total") %></th>
			<td id="$TableSubTotalID">$SubTotal.Nice</td>
			<td>&nbsp;</td>
		</tr>
		<% if Modifiers %>
			<% control Modifiers %>
				<% if ShowInTable %>
					<tr id="$TableID" class="$Classes">
						<th id="$TableTitleID" colspan="3" scope="row">
							<% if Link %>
								<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$TableTitle) %>">$TableTitle</a>
							<% else %>
								$TableTitle
							<% end_if %>
						</th>
						<td id="$TableTotalID">$TableValue.Nice</td>
						<td>
							<% if CanRemove %>
								<strong>
									<a class="ajaxQuantityLink" href="$removeLink" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your order"),$TableTitle) %>">
										<img src="shop/images/remove.gif" alt="x"/>
									</a>
								</strong>
							<% end_if %>
						</td>
					</tr>
				<% end_if %>
			<% end_control %>
		<% end_if %>
		<tr class="gap Total">
			<th colspan="3" scope="row"><% _t("TOTAL","Total") %></th>
			<td id="$TableTotalID"><span class="value">$Total.Nice</span> <span class="currency">$Currency</span></td>
		</tr>
	</tfoot>
</table>
<% else %>
<p class="message warning"><% _t("NOITEMS","There are no items in your cart.") %></p>
<% end_if %>
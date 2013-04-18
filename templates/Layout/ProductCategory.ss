<% require themedCSS(productcategory) %>
<div id="Sidebar">
	<% include ProductMenu %>
	<div class="cart">
		<% include SideCart %>
	</div>
</div>

<div id="ProductGroup" class="typography">
	<h1 class="pageTitle">$Title</h1>
	<% if Content %>
		<div>
			$Content
		</div>
	<% end_if %>

	<% if Products %>
		<div id="Products" class="category">
			<div class="resultsBar">
				<% if SortLinks %><span class="sortOptions"><% _t('ProductGroup.SORTBY','sort by') %> <% loop SortLinks %><a href="$Link" class="sortlink $Current">$Name</a> <% end_loop %></span><% end_if %>
			</div>
			<div class="clear"><!-- --></div>
			<ul class="productList">
				<% loop Products %>
					<% include ProductGroupItem %>
				<% end_loop %>
			</ul>
			<div class="clear"><!-- --></div>
			<% include ProductGroupPagination %>
		</div>
	<% end_if %>
</div>
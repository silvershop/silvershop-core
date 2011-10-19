<% include ProductMenu %>
<div id="ProductGroup">
	<h1 class="pageTitle">$Title</h1>

	<% if Content %>
		<div class="typography">
			$Content
		</div>
	<% end_if %>

	<% if Products %>
		<div id="Products" class="category">
			<div class="resultsBar typography">
				<% if SortLinks %><span class="sortOptions">Sort by <% control SortLinks %><a href="$Link" class="sortlink $Current">$Name</a> <% end_control %></span><% end_if %>
			</div>
			<div class="clear"><!-- --></div>
			<ul class="productList">
				<% control Products %>
					<% include ProductGroupItem %>
				<% end_control %>
			</ul>
			<div class="clear"><!-- --></div>
		</div>
		<% include ProductGroupPagination %>
	<% end_if %>

</div>
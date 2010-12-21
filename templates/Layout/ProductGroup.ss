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
	<% end_if %>

	<% if Products.MoreThanOnePage %>
	<div id="PageNumbers">
		<p>
			<% if Products.NotFirstPage %>
				<a class="prev" href="$Products.PrevLink" title="View the previous page">previous</a>
			<% end_if %>
 
			<span>
		    		<% control Products.PaginationSummary(4) %>
					<% if CurrentBool %>
						$PageNum
					<% else %>
						<% if Link %>
							<a href="$Link" title="View page number $PageNum">$PageNum</a>
						<% else %>
							&hellip;
						<% end_if %>
					<% end_if %>
				<% end_control %>
			</span>
 
			<% if Products.NotLastPage %>
				<a class="next" href="$Products.NextLink" title="View the next page">next</a>
			<% end_if %>
		</p>
	</div>
	<% end_if %>

</div>

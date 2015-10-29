<% if $Products.MoreThanOnePage %>
<div id="PageNumbers">
	<p><% _t('ProductGroup.PAGE','page') %>:
		<% if $Products.NotFirstPage %>
			<a class="prev" href="$Products.PrevLink" title="<%t ProductGroup.VIEW_PREVIOUS "View the previous page" %>"><% _t('ProductGroup.PREVIOUS','previous') %></a>
		<% end_if %>

		<span>
	    	<% loop $Products.PaginationSummary(4) %>
				<% if $CurrentBool %>
					$PageNum
				<% else %>
					<% if $Link %>
						<a href="$Link" title="<%t ProductGroup.VIEW_PAGE "View page number {pageNum}" pageNum=$PageNum %>">$PageNum</a>
					<% else %>
						&hellip;
					<% end_if %>
				<% end_if %>
			<% end_loop %>
		</span>

		<% if $Products.NotLastPage %>
			<a class="next" href="$Products.NextLink" title="<%t ProductGroup.VIEW_NEXT "View the next page" %>"><% _t('ProductGroup.NEXT','next') %></a>
		<% end_if %>
	</p>
</div>
<% end_if %>

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
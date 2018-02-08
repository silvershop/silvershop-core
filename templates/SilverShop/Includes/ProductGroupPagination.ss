<% if $Products.MoreThanOnePage %>
<div id="PageNumbers">
    <p><%t SilverShop\Includes\ProductGroup.Page "Page" %>:
        <% if $Products.NotFirstPage %>
            <a class="prev" href="$Products.PrevLink" title="<%t SilverShop\Includes\ProductGroup.ViewPrevious "View the previous page" %>"><%t SilverShop\Includes\ProductGroup.Previous "previous" %></a>
        <% end_if %>

        <span>
            <% loop $Products.PaginationSummary(4) %>
                <% if $CurrentBool %>
                    $PageNum
                <% else %>
                    <% if $Link %>
                        <a href="$Link" title="<%t SilverShop\Includes\ProductGroup.ViewPage "View page number {pageNum}" pageNum=$PageNum %>">$PageNum</a>
                    <% else %>
                        &hellip;
                    <% end_if %>
                <% end_if %>
            <% end_loop %>
        </span>

        <% if $Products.NotLastPage %>
            <a class="next" href="$Products.NextLink" title="<%t SilverShop\Includes\ProductGroup.ViewNext "View the next page" %>"><%t SilverShop\Includes\ProductGroup.Next "next" %></a>
        <% end_if %>
    </p>
</div>
<% end_if %>

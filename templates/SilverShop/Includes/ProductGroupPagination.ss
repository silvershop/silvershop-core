<% if $Products.MoreThanOnePage %>
<nav class="silvershop-pagination">
    <p class="silvershop-pagination__inner"><span class="silvershop-pagination__label"><%t SilverShop\Includes\ProductGroup.Page "Page" %>:</span>
        <% if $Products.NotFirstPage %>
            <a class="silvershop-pagination__prev" href="$Products.PrevLink" title="<%t SilverShop\Includes\ProductGroup.ViewPrevious "View the previous page" %>"><%t SilverShop\Includes\ProductGroup.Previous "previous" %></a>
        <% end_if %>

        <span class="silvershop-pagination__pages">
            <% loop $Products.PaginationSummary(4) %>
                <% if $CurrentBool %>
                    <span class="silvershop-pagination__page silvershop-pagination__page--current">$PageNum</span>
                <% else %>
                    <% if $Link %>
                        <a class="silvershop-pagination__page" href="$Link" title="<%t SilverShop\Includes\ProductGroup.ViewPage "View page number {pageNum}" pageNum=$PageNum %>">$PageNum</a>
                    <% else %>
                        <span class="silvershop-pagination__page silvershop-pagination__page--ellipsis">&hellip;</span>
                    <% end_if %>
                <% end_if %>
            <% end_loop %>
        </span>

        <% if $Products.NotLastPage %>
            <a class="silvershop-pagination__next" href="$Products.NextLink" title="<%t SilverShop\Includes\ProductGroup.ViewNext "View the next page" %>"><%t SilverShop\Includes\ProductGroup.Next "next" %></a>
        <% end_if %>
    </p>
</nav>
<% end_if %>

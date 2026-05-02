<div id="ProductGroup" class="silvershop-typography">
    <h1 class="silvershop-pageTitle">$Title</h1>
    <% if $Content %>
        <div>
            $Content
        </div>
    <% end_if %>

    <% if $Products %>
        <div id="Products" class="silvershop-category">
            <% include SilverShop\ListSorter\Includes\Sorter %>
            <div class="silvershop-clear"><!-- --></div>
            <ul class="silvershop-productList">
                <% loop $Products %>
                    <% include SilverShop\Includes\ProductGroupItem %>
                <% end_loop %>
            </ul>
            <div class="silvershop-clear"><!-- --></div>
            <% include SilverShop\Includes\ProductGroupPagination %>
        </div>
    <% end_if %>
</div>
<% include SilverShop\Includes\SideBar %>

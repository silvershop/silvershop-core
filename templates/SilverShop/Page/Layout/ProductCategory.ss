<div class="silvershop-product-category silvershop-typography">
    <h1 class="silvershop-product-category__title">$Title</h1>
    <% if $Content %>
        <div class="silvershop-product-category__content">
            $Content
        </div>
    <% end_if %>

    <% if $Products %>
        <div class="silvershop-product-category__products">
            <% include SilverShop\ListSorter\Includes\Sorter %>
            <div class="silvershop-product-category__clear"><!-- --></div>
            <ul class="silvershop-product-category__list">
                <% loop $Products %>
                    <% include SilverShop\Includes\ProductGroupItem %>
                <% end_loop %>
            </ul>
            <div class="silvershop-product-category__clear"><!-- --></div>
            <% include SilverShop\Includes\ProductGroupPagination %>
        </div>
    <% end_if %>
</div>
<% include SilverShop\Includes\SideBar %>

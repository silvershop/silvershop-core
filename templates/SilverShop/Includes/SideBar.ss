<aside class="silvershop-sidebar">
    <% if $GroupsMenu %>
        <% include SilverShop\Includes\ProductMenu %>
    <% else %>
        <% with $Parent %>
            <% include SilverShop\Includes\ProductMenu %>
        <% end_with %>
    <% end_if %>
    <div class="silvershop-sidebar__cart">
        <% include SilverShop\Cart\SideCart %>
    </div>
</aside>

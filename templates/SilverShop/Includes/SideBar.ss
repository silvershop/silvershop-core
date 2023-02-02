<div id="Sidebar">
    <% if $GroupsMenu %>
        <% include SilverShop\Includes\ProductMenu %>
    <% else %>
        <% with $Parent %>
            <% include SilverShop\Includes\ProductMenu %>
        <% end_with %>
    <% end_if %>
    <% include SilverShop\Cart\SideCart %>
</div>

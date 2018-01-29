<div id="Sidebar">
    <% if $GroupsMenu %>
        <% include SilverShop\Core\ProductMenu %>
    <% else %>
        <% with $Parent %>
            <% include SilverShop\Core\ProductMenu %>
        <% end_with %>
    <% end_if %>
    <div class="cart">
      <% include SilverShop\Core\Cart\SideCart %>
  </div>
</div>

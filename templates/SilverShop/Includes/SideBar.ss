<div id="Sidebar">
    <% if $GroupsMenu %>
        <% include SilverShop\ProductMenu %>
    <% else %>
        <% with $Parent %>
            <% include SilverShop\ProductMenu %>
        <% end_with %>
    <% end_if %>
    <div class="cart">
      <% include SilverShop\Cart\SideCart %>
  </div>
</div>

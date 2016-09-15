<div id="Sidebar">
    <% if $GroupsMenu %>
        <% include ProductMenu %>
    <% else %>
        <% with $Parent %>
            <% include ProductMenu %>
        <% end_with %>
    <% end_if %>
  <div class="cart">
      <% include SideCart %>
  </div>
</div>

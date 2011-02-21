<span class="ajaxAddToCartLink">
	<a class="ajaxRemove <% if IsInCart %>show<% else %>doNotShow<% end_if %>" href="$RemoveLinkAjax"><% _t('Remove from Cart') %></a>
	<a class="ajaxAdd <% if IsInCart %>doNotShow<% else %>show<% end_if %>" href="$AddLinkAjax"><% _t('Add to Cart') %></a>
</span>

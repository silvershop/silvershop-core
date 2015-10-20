<div class="productItem">
	<% if $Image %>
		<a href="$Link" title="<% sprintf(_t("READMORE","Click here to read more on &quot;%s&quot;"),$Title) %>">
			<img src="$Image.Thumbnail.URL" alt="<% sprintf(_t("IMAGE","%s image"),$Title) %>" />
		</a>
	<% else %>
		<a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>" class="noimage"><!-- no image --></a>
	<% end_if %>
	<h3 class="productTitle"><a href="$Link" title="<% sprintf(_t("READMORE"),$Title) %>">$Title</a></h3>
	<% if $Model %><p><strong><% _t("MODEL","Model") %>:</strong> $Model.XML</p><% end_if %>
	<div>
		<% if $Price %><strong class="price">$Price.Nice</strong> <span class="currency">$Currency</span><% end_if %>
		<% if $View %>
			<div class="view">
				<a href="$Link" title="<% sprintf(_t("VIEW","View &quot;%s&quot;"),$Title) %>">
					<% _t("VIEW","View Product") %>
				</a>
			</div>
		<% else %>
			<% if $canPurchase %>
			<div class="add">
				<a href="$addLink" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title) %>">
					<% _t("ADDLINK","Add to Cart") %>
				</a>
			</div>
			<% end_if %>
		<% end_if %>
	</div>
</div>

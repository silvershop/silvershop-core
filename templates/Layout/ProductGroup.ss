<% include ProductMenu %>

<div id="ProductGroup">
	<div id="Breadcrumbs" class="typography">
   	<p>$Breadcrumbs</p>
	</div>
	
	<h2 class="pageTitle">$Title</h2>
	
	<% if Content %>
		<div class="typography">
			$Content
		</div>
	<% end_if %>
	
	<% if FeaturedProducts %>
		<h3 class="categoryTitle"><% _t("FEATURED","Featured Products") %></h3>
		<div id="FeaturedProducts" class="category">
			<div class="resultsBar typography">
				<p class="resultsShowing">Showing <span class="firstProductIndex">1</span> to <span class="lastProductIndex">$FeaturedProducts.Count</span> of <span class="productsTotal">$FeaturedProducts.Count</span> products</p>
			</div>
			<div class="clear"><!-- --></div>
			<ul class="productList">
				<% control FeaturedProducts %>
					<% include ProductGroupItem %>
				<% end_control %>
			</ul>
			<div class="clear"><!-- --></div>
		</div>
	<% end_if %>
	
	<% if NonFeaturedProducts %>
		<h3 class="categoryTitle"><% _t("OTHER","Other Products") %></h3>
		<div id="NonFeaturedProducts" class="category">
			<div class="resultsBar typography">
				<p class="resultsShowing">Showing <span class="firstProductIndex">1</span> to <span class="lastProductIndex">$NonFeaturedProducts.Count</span> of <span class="productsTotal">$NonFeaturedProducts.Count</span> products</p>
			</div>
			<div class="clear"><!-- --></div>
			<ul class="productList">
				<% control NonFeaturedProducts %>
					<% include ProductGroupItem %>
				<% end_control %>
			</ul>
			<div class="clear"><!-- --></div>
		</div>
	<% end_if %>
</div>

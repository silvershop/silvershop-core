<% require themedCSS(product,shop) %>
<div id="Sidebar">
	<% with $Parent %>
		<% include ProductMenu %>
	<% end_with %>
	<div class="cart">
		<% include SideCart %>
	</div>
</div>
<div id="Product" class="typography">
	<h1 class="pageTitle">$Title</h1>
	<div class="breadcrumbs">$Breadcrumbs</div>
	<div class="productDetails">
		<% if $Image.ContentImage %>
			<img class="productImage" src="$Image.ContentImage.URL" alt="<%t Product.ImageAltText "{Title} image" Title=$Title %>" />
		<% else %>
			<div class="noimage"><%t Product.NoImage "no image" %></div>
		<% end_if %>
		<% if $InternalItemID %><p><%t Product.Code "Product Code" %> : {$InternalItemID}</p><% end_if %>
		<% if $Model %><p><%t Product.Model "Model" %> : $Model.XML</p><% end_if %>
		<% if $Size %><p><%t Product.Size "Size" %> : $Size.XML</p><% end_if %>
		<% if $PriceRange %>
			<div class="price">
				<strong class="value">$PriceRange.Min.Nice</strong>
				<% if $PriceRange.HasRange %>
					- <strong class="value">$PriceRange.Max.Nice</strong>
				<% end_if %>
				<span class="currency">$Price.Currency</span>
			</div>
		<% else %>
			<% if $Price %>
				<div class="price">
					<strong class="value">$Price.Nice</strong> <span class="currency">$Price.Currency</span>
				</div>
			<% end_if %>
		<% end_if %>
		$Form
	</div>
	<% if $Content %>
		<div class="productContent typography">
			$Content
		</div>
	<% end_if %>
</div>

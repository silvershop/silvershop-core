# Featured Products

Products can be flagged as 'featured' in the CMS. These flagged products can then be presented in specific ways.

You may want to display a small set of featured products somewhere on your site, such as the home page. Here's how:

[mysite]/code/HomePage.php:

	:::php
	class HomePage extends ProductCategory{
		//...
	}
	class HomePage_Controller extends ProductCategory_Controller{
		
		/**
		* Get all products marked as featured that can be purchased.
		*/
		function FeaturedProducts(){
			$filter = '"Featured" = 1 AND "AllowPurchase" = 1';
			return DataObject::get('Product',$filter);
		}
		
	}

[mysite]/templates/Layout/HomePage.ss

	<% if FeaturedProducts %>
		<div class="featuredproducts">
			<% control FeaturedProducts %>
				<% include ProductGroupItem %>
			<% end_control %>
		</div>
	<% end_if %>

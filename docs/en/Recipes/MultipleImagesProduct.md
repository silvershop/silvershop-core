# Multiple Images for Each Product

Decorate Product, and add many_many AdditionalImages.
Update CMS fields, so images can be added.

[mysite]/code/MultipleProductImages.php

	:::php
	class MultipleProductImages extends DataObjectDecorator{
	
		function extraStatics(){
			return array(
				'many_many' => array(
							'AdditionalImages' => 'Product_Image'
						)
			);
		}
		
		function updateCMSFields($fields){
			
			$fields->addFieldToTab('Root.Main.Images',
				new ManyManyComplexTableField(null,'AdditionalImages','Image')
			);
		
		}
		
	}

[mysite]/_config.php, add:

	:::php
	Object::add_extension('Product','MultipleProductImages');

[mysite]/templates/Includes/AdditionalImages.ss

	<% if AdditionalImages %>
		<% control AdditionalImages %>
			$Me
		<% end_control %>
	<% end_if %>

Add <% include AdditionalImages %> somewhere in your Product.ss template.

<div class="bad" markdown="1">
This is rough, untested code. Comment below if you need help to actually get it working.
</div>

<div class="info" markdown="1">
Note that we use many_many instead of has_many. This simply allows the same image to be used
in other ways on the site.
</div>
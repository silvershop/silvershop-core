**Note:** This functionality can be found in: https://github.com/markguinn/silverstripe-shop-extendedimages

Decorate Product, and add many_many AdditionalImages. Update CMS fields, so images can be added.

[mysite]/code/MultipleProductImages.php

```php
class MultipleProductImages extends DataExtension{

	private static $many_many = array(
		'AdditionalImages' => 'Image'
	);
	
	function updateCMSFields(FieldList $fields){
		$fields->addFieldToTab('Root.Images',
			new GridField(
				'AdditionalImages',
				'Images',
				$this->owner->AdditionalImages()
			)
		);
	}
	
}
```

Add the extension in `_config/config.yml`

```yaml
Product:
	extensions:
		- MultipleProductImages
```

`[mysite]/templates/Includes/AdditionalImages.ss`

```html
<% if AdditionalImages %>
	<% loop AdditionalImages %>
		$Me
	<% end_loop %>
<% end_if %>
```

Add <% include AdditionalImages %> somewhere in your Product.ss template.
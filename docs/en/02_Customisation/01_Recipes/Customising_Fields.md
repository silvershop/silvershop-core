# Custom fields for orders, customers

You may want to store additional information for each customer.
In a nutshell you need to update your DataModel, and any places where the 
data is entered. This can generally be done by creating extensions.

First read the [SilverStripe documentation about Extensions](http://docs.silverstripe.org/en/developer_guides/model/extending_dataobjects/).

## Customer

Customer fields are saved to both Members and Orders.

### Add database field(s) to the model

In [mysite]/code/ExtendedCustomer.php

	:::php
	<?php
	class ExtendedCustomer extends DataExtension{
		private static $db = array(
			'MyExtraField' => 'Varchar'
		);
	}
	

To your _config.php file, add:

	:::php
	Object::add_extension('Member','ExtendedCustomer');
	Object::add_extension('Order','ExtendedCustomer');

### Update form(s)

To let your website visitors actually enter the data, you will need to modify
various forms.

In [mysite]/code/ExtendedOrderForm.php

	:::php
	<?php
	class ExtendedOrderForm extends Extension{
	
		function updateForm($form){
			$leftfields = $form->Fields()->fieldByName("LeftOrder")->FieldList();
			$leftfields->insertAfter(new TextField("MyExtraField","My Extra Field"),"Country");
		}
	
	}
	
To your _config.php file, add:

	:::php
	Object::add_extension('OrderForm','ExtendedOrderForm');

<div class="warning" markdown="1">
Note: remember to flush the class manifest by adding ?flush=1 to your site url.
</div>